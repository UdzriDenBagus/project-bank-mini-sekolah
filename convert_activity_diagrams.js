import fs from 'fs';
import {
  Document,
  Packer,
  Paragraph,
  TextRun,
  HeadingLevel,
  Table,
  TableRow,
  TableCell,
  WidthType,
  BorderStyle,
  AlignmentType,
  ShadingType,
  Header,
  Footer,
  PageNumber,
  convertInchesToTwip
} from 'docx';

function convertMarkdownToDocx(mdPath, docxPath, docTitle) {
  const content = fs.readFileSync(mdPath, 'utf-8');
  const lines = content.split(/\r?\n/);
  const children = [];

  let inCodeBlock = false;
  let codeBlockLines = [];
  let tableBuffer = [];

  function flushCodeBlock() {
    if (codeBlockLines.length === 0) return;
    const fullText = codeBlockLines.join('\n');
    codeBlockLines = [];
    
    const para = new Paragraph({
      spacing: { before: 120, after: 120, line: 240 },
      shading: { type: ShadingType.CLEAR, fill: "F1F5F9" },
      border: {
        left: { style: BorderStyle.SINGLE, size: 24, color: "0284C7" },
        top: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
        right: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
        bottom: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" }
      },
      children: fullText.split('\n').map((line, idx, arr) => {
        return new TextRun({
          text: line + (idx < arr.length - 1 ? '\r\n' : ''),
          font: "Consolas",
          size: 15,
          color: "0F172A"
        });
      })
    });
    children.push(para);
  }

  function parseFormattedText(text) {
    const runs = [];
    const regex = /(\*\*[^*]+\*\*|`[^`]+`|\*[^*]+\*)/g;
    let lastIdx = 0;
    let match;

    while ((match = regex.exec(text)) !== null) {
      if (match.index > lastIdx) {
        runs.push(new TextRun({
          text: text.substring(lastIdx, match.index),
          font: "Calibri",
          size: 22,
          color: "1E293B"
        }));
      }

      const token = match[0];
      if (token.startsWith('**') && token.endsWith('**')) {
        runs.push(new TextRun({
          text: token.slice(2, -2),
          bold: true,
          font: "Calibri",
          size: 22,
          color: "0F172A"
        }));
      } else if (token.startsWith('`') && token.endsWith('`')) {
        runs.push(new TextRun({
          text: token.slice(1, -1),
          font: "Consolas",
          size: 19,
          color: "BE185D",
          shading: { type: ShadingType.CLEAR, fill: "F3F4F6" }
        }));
      } else if (token.startsWith('*') && token.endsWith('*')) {
        runs.push(new TextRun({
          text: token.slice(1, -1),
          italics: true,
          font: "Calibri",
          size: 22,
          color: "334155"
        }));
      }

      lastIdx = regex.lastIndex;
    }

    if (lastIdx < text.length) {
      runs.push(new TextRun({
        text: text.substring(lastIdx),
        font: "Calibri",
        size: 22,
        color: "1E293B"
      }));
    }

    return runs.length > 0 ? runs : [new TextRun({ text, font: "Calibri", size: 22, color: "1E293B" })];
  }

  function flushTable() {
    if (tableBuffer.length === 0) return;

    const rawRows = tableBuffer.filter(r => !r.match(/^\|?\s*:?-+:?\s*(\|?\s*:?-+:?\s*)+\|?$/));
    tableBuffer = [];

    if (rawRows.length === 0) return;

    const parsedMatrix = rawRows.map(rowStr => {
      let clean = rowStr.trim();
      if (clean.startsWith('|')) clean = clean.substring(1);
      if (clean.endsWith('|')) clean = clean.substring(0, clean.length - 1);
      return clean.split('|').map(c => c.trim());
    });

    const numCols = Math.max(...parsedMatrix.map(r => r.length));

    const tableRows = parsedMatrix.map((rowCells, rIdx) => {
      const isHeader = rIdx === 0;
      const cells = [];
      for (let c = 0; c < numCols; c++) {
        const cellText = rowCells[c] || '';
        const runs = parseFormattedText(cellText);
        if (isHeader) {
          runs.forEach(r => {
            r.bold = true;
            r.color = "FFFFFF";
          });
        }

        cells.push(new TableCell({
          shading: {
            type: ShadingType.CLEAR,
            fill: isHeader ? "1E3A8A" : (rIdx % 2 === 1 ? "FFFFFF" : "F8FAFC")
          },
          margins: { top: 120, bottom: 120, left: 150, right: 150 },
          borders: {
            top: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
            bottom: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
            left: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" },
            right: { style: BorderStyle.SINGLE, size: 4, color: "CBD5E1" }
          },
          children: [
            new Paragraph({
              alignment: isHeader ? AlignmentType.CENTER : AlignmentType.LEFT,
              children: runs
            })
          ]
        }));
      }

      return new TableRow({
        tableHeader: isHeader,
        children: cells
      });
    });

    const table = new Table({
      width: { size: 100, type: WidthType.PERCENTAGE },
      rows: tableRows
    });

    children.push(new Paragraph({ spacing: { before: 100, after: 100 }, children: [] }));
    children.push(table);
    children.push(new Paragraph({ spacing: { before: 100, after: 100 }, children: [] }));
  }

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    if (line.startsWith('```')) {
      if (inCodeBlock) {
        flushCodeBlock();
        inCodeBlock = false;
      } else {
        if (tableBuffer.length > 0) flushTable();
        inCodeBlock = true;
        codeBlockLines = [];
      }
      continue;
    }

    if (inCodeBlock) {
      codeBlockLines.push(line);
      continue;
    }

    if (line.trim().startsWith('|') && line.trim().endsWith('|')) {
      tableBuffer.push(line);
      continue;
    } else {
      if (tableBuffer.length > 0) {
        flushTable();
      }
    }

    if (!line.trim()) {
      continue;
    }

    if (line.startsWith('# ')) {
      const text = line.substring(2).trim();
      children.push(new Paragraph({
        heading: HeadingLevel.HEADING_1,
        spacing: { before: 300, after: 140 },
        children: [
          new TextRun({
            text,
            bold: true,
            font: "Calibri",
            size: 32,
            color: "1E3A8A"
          })
        ]
      }));
      continue;
    }

    if (line.startsWith('## ')) {
      const text = line.substring(3).trim();
      children.push(new Paragraph({
        heading: HeadingLevel.HEADING_2,
        spacing: { before: 240, after: 120 },
        border: {
          bottom: { style: BorderStyle.SINGLE, size: 8, color: "1E3A8A" }
        },
        children: [
          new TextRun({
            text,
            bold: true,
            font: "Calibri",
            size: 26,
            color: "1E3A8A"
          })
        ]
      }));
      continue;
    }

    if (line.startsWith('### ')) {
      const text = line.substring(4).trim();
      children.push(new Paragraph({
        heading: HeadingLevel.HEADING_3,
        spacing: { before: 200, after: 100 },
        children: [
          new TextRun({
            text,
            bold: true,
            font: "Calibri",
            size: 24,
            color: "0369A1"
          })
        ]
      }));
      continue;
    }

    if (line.trim() === '---') {
      children.push(new Paragraph({
        spacing: { before: 120, after: 120 },
        border: {
          bottom: { style: BorderStyle.SINGLE, size: 6, color: "E2E8F0" }
        },
        children: []
      }));
      continue;
    }

    if (line.trim().startsWith('- ') || line.trim().startsWith('* ')) {
      const text = line.trim().substring(2);
      children.push(new Paragraph({
        bullet: { level: 0 },
        spacing: { before: 40, after: 40, line: 260 },
        children: parseFormattedText(text)
      }));
      continue;
    }

    const numMatch = line.trim().match(/^(\d+)\.\s+(.*)$/);
    if (numMatch) {
      const text = numMatch[2];
      children.push(new Paragraph({
        spacing: { before: 40, after: 40, line: 260 },
        indent: { left: convertInchesToTwip(0.3), hanging: convertInchesToTwip(0.3) },
        children: [
          new TextRun({ text: numMatch[1] + '.  ', bold: true, font: "Calibri", size: 22, color: "1E3A8A" }),
          ...parseFormattedText(text)
        ]
      }));
      continue;
    }

    children.push(new Paragraph({
      spacing: { before: 60, after: 60, line: 260 },
      alignment: AlignmentType.JUSTIFY,
      children: parseFormattedText(line.trim())
    }));
  }

  if (tableBuffer.length > 0) flushTable();
  if (inCodeBlock) flushCodeBlock();

  const doc = new Document({
    creator: "Esa Nur'udzri Yunan Arifianto",
    title: docTitle,
    styles: {
      default: {
        document: {
          run: {
            font: "Calibri",
            size: 22,
            color: "1E293B"
          }
        }
      }
    },
    sections: [
      {
        properties: {
          page: {
            margin: {
              top: convertInchesToTwip(1),
              bottom: convertInchesToTwip(1),
              left: convertInchesToTwip(1.2),
              right: convertInchesToTwip(1)
            }
          }
        },
        headers: {
          default: new Header({
            children: [
              new Paragraph({
                alignment: AlignmentType.RIGHT,
                children: [
                  new TextRun({
                    text: docTitle + " | SMK Negeri 1 Bantul",
                    font: "Calibri",
                    size: 16,
                    color: "94A3B8"
                  })
                ]
              })
            ]
          })
        },
        footers: {
          default: new Footer({
            children: [
              new Paragraph({
                alignment: AlignmentType.CENTER,
                children: [
                  new TextRun({
                    text: "Halaman ",
                    font: "Calibri",
                    size: 18,
                    color: "64748B"
                  }),
                  new TextRun({
                    children: [PageNumber.CURRENT],
                    font: "Calibri",
                    size: 18,
                    color: "64748B",
                    bold: true
                  }),
                  new TextRun({
                    text: " dari ",
                    font: "Calibri",
                    size: 18,
                    color: "64748B"
                  }),
                  new TextRun({
                    children: [PageNumber.TOTAL_PAGES],
                    font: "Calibri",
                    size: 18,
                    color: "64748B",
                    bold: true
                  })
                ]
              })
            ]
          })
        },
        children: children
      }
    ]
  });

  console.log("Generating DOCX for:", docTitle);
  Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(docxPath, buffer);
    console.log("SUCCESS! Written to:", docxPath);
  }).catch(err => {
    console.error("ERROR generating docx:", err);
  });
}

// Convert Complete Activity Diagram Document
convertMarkdownToDocx(
  'c:/laragon/www/bank-mini-udzri/ACTIVITY_DIAGRAM_KOMPLIT_PER_FITUR.md',
  'c:/laragon/www/bank-mini-udzri/ACTIVITY_DIAGRAM_KOMPLIT_PER_FITUR.docx',
  'Activity Diagram Komplit Per Fitur E-Teller Bank Mini'
);
