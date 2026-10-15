from __future__ import annotations

import os
import re
import tempfile
import textwrap
import zipfile
from pathlib import Path
from xml.sax.saxutils import escape

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "docs" / "Arquitectura-Devioz-Modelo-4mas1.docx"
NAVY = "15324B"
TEAL = "087E8B"
MINT = "E8F5F2"
PALE = "F1F5F9"
INK = "203040"
MUTED = "5D6B78"
ORANGE = "E07A3F"
RED = "B54747"
GREEN = "347A58"


def xml_text(value: object) -> str:
    return escape(str(value), {'"': "&quot;"})


def run(text: str, bold: bool = False, italic: bool = False, color: str | None = None, size: int | None = None) -> str:
    props = []
    if bold:
        props.append("<w:b/>")
    if italic:
        props.append("<w:i/>")
    if color:
        props.append(f'<w:color w:val="{color}"/>')
    if size:
        props.append(f'<w:sz w:val="{size}"/><w:szCs w:val="{size}"/>')
    rpr = f"<w:rPr>{''.join(props)}</w:rPr>" if props else ""
    return f"<w:r>{rpr}<w:t xml:space=\"preserve\">{xml_text(text)}</w:t></w:r>"


class Doc:
    def __init__(self):
        self.body: list[str] = []
        self.rels: list[str] = []
        self.images: list[tuple[str, bytes]] = []
        self.image_id = 10
        self.heading_count = 0

    def p(self, text: str = "", style: str | None = None, bold: bool = False, italic: bool = False,
          color: str | None = None, size: int | None = None, align: str | None = None,
          before: int = 0, after: int = 120, keep: bool = False):
        ppr = []
        if style:
            ppr.append(f'<w:pStyle w:val="{style}"/>')
        if align:
            ppr.append(f'<w:jc w:val="{align}"/>')
        if before or after:
            ppr.append(f'<w:spacing w:before="{before}" w:after="{after}" w:line="276" w:lineRule="auto"/>')
        if keep:
            ppr.append("<w:keepNext/>")
        ppr_xml = f"<w:pPr>{''.join(ppr)}</w:pPr>" if ppr else ""
        self.body.append(f"<w:p>{ppr_xml}{run(text, bold, italic, color, size)}</w:p>")

    def rich(self, parts: list[tuple[str, bool]], after: int = 100):
        self.body.append(f'<w:p><w:pPr><w:spacing w:after="{after}" w:line="276"/></w:pPr>{"".join(run(t, b) for t, b in parts)}</w:p>')

    def heading(self, text: str, level: int = 1):
        self.heading_count += 1
        style = f"Heading{level}"
        self.body.append(f'<w:p><w:pPr><w:pStyle w:val="{style}"/><w:keepNext/></w:pPr>{run(text)}</w:p>')

    def bullet(self, text: str, level: int = 0):
        indent = 420 + (level * 360)
        self.body.append(f'<w:p><w:pPr><w:ind w:left="{indent}" w:hanging="240"/><w:spacing w:after="55" w:line="260"/></w:pPr>{run("•  " + text)}</w:p>')

    def numbered(self, number: str, text: str):
        self.rich([(number + "  ", True), (text, False)], after=55)

    def code(self, text: str):
        for line in text.splitlines():
            self.body.append(f'<w:p><w:pPr><w:pStyle w:val="CodeBlock"/><w:spacing w:after="0"/></w:pPr>{run(line or " ", color="33546C", size=18)}</w:p>')

    def note(self, label: str, text: str, color: str = TEAL):
        self.body.append(f'<w:p><w:pPr><w:pBdr><w:left w:val="single" w:sz="20" w:space="8" w:color="{color}"/></w:pBdr><w:ind w:left="200"/><w:spacing w:before="100" w:after="160"/></w:pPr>{run(label + "  ", True, color=color)}{run(text)}</w:p>')

    def page_break(self):
        self.body.append('<w:p><w:r><w:br w:type="page"/></w:r></w:p>')

    def table(self, headers: list[str], rows: list[list[str]], widths: list[int] | None = None, font_size: int = 18):
        n = len(headers)
        if widths is None:
            widths = [9360 // n] * n
            widths[-1] += 9360 - sum(widths)
        border = '<w:tblBorders><w:top w:val="single" w:sz="5" w:color="D6E0E8"/><w:left w:val="single" w:sz="5" w:color="D6E0E8"/><w:bottom w:val="single" w:sz="5" w:color="D6E0E8"/><w:right w:val="single" w:sz="5" w:color="D6E0E8"/><w:insideH w:val="single" w:sz="4" w:color="E2E8EE"/><w:insideV w:val="single" w:sz="4" w:color="E2E8EE"/></w:tblBorders>'
        parts = [f'<w:tbl><w:tblPr><w:tblW w:w="9360" w:type="dxa"/><w:tblLayout w:type="fixed"/>{border}<w:tblCellMar><w:top w:w="90" w:type="dxa"/><w:start w:w="100" w:type="dxa"/><w:bottom w:w="90" w:type="dxa"/><w:end w:w="100" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>']
        parts.extend(f'<w:gridCol w:w="{w}"/>' for w in widths)
        parts.append('</w:tblGrid>')
        all_rows = [headers] + rows
        for ri, row in enumerate(all_rows):
            parts.append('<w:tr><w:trPr><w:cantSplit/></w:trPr>')
            for ci, value in enumerate(row):
                shade = NAVY if ri == 0 else ("F5F8FA" if ri % 2 else "FFFFFF")
                color = "FFFFFF" if ri == 0 else INK
                parts.append(f'<w:tc><w:tcPr><w:tcW w:w="{widths[ci]}" w:type="dxa"/><w:shd w:fill="{shade}"/></w:tcPr>')
                lines = str(value).split("\n")
                for li, line in enumerate(lines):
                    parts.append(f'<w:p><w:pPr><w:spacing w:after="35" w:line="235"/></w:pPr>{run(line, bold=(ri == 0), color=color, size=font_size)}</w:p>')
                parts.append('</w:tc>')
            parts.append('</w:tr>')
        parts.append('</w:tbl><w:p><w:pPr><w:spacing w:after="80"/></w:pPr></w:p>')
        self.body.append("".join(parts))

    def image(self, path: Path, width_inches: float, caption: str):
        data = path.read_bytes()
        name = f"image{len(self.images) + 1}.png"
        rid = f"rId{self.image_id}"
        self.image_id += 1
        self.images.append((name, data))
        self.rels.append(f'<Relationship Id="{rid}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/{name}"/>')
        w = int(width_inches * 914400)
        h = int(w * Image.open(path).height / Image.open(path).width)
        cx = 990000 + self.image_id
        self.body.append(
            f'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="80" w:after="30"/></w:pPr>'
            f'<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            f'<wp:extent cx="{w}" cy="{h}"/><wp:docPr id="{cx}" name="{xml_text(caption)}"/>'
            f'<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            f'<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            f'<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="0" name="{name}"/><pic:cNvPicPr/></pic:nvPicPr>'
            f'<pic:blipFill><a:blip r:embed="{rid}"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            f'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="{w}" cy="{h}"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            f'</pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>'
        )
        self.p(caption, style="Caption", align="center", after=160)


def font(size: int, bold: bool = False):
    candidates = [r"C:\Windows\Fonts\arialbd.ttf" if bold else r"C:\Windows\Fonts\arial.ttf",
                  r"C:\Windows\Fonts\calibrib.ttf" if bold else r"C:\Windows\Fonts\calibri.ttf"]
    for candidate in candidates:
        if Path(candidate).exists():
            return ImageFont.truetype(candidate, size)
    return ImageFont.load_default()


def diagram_canvas(title: str, subtitle: str, width: int = 1500, height: int = 760):
    image = Image.new("RGB", (width, height), "white")
    draw = ImageDraw.Draw(image)
    draw.rectangle((0, 0, width, 88), fill=f"#{NAVY}")
    draw.text((44, 22), title, font=font(32, True), fill="white")
    draw.text((46, 104), subtitle, font=font(19), fill=f"#{MUTED}")
    return image, draw


def wrap_for_box(draw, text, fnt, max_width):
    words = text.split()
    lines, line = [], ""
    for word in words:
        trial = (line + " " + word).strip()
        if draw.textbbox((0, 0), trial, font=fnt)[2] <= max_width or not line:
            line = trial
        else:
            lines.append(line)
            line = word
    if line:
        lines.append(line)
    return lines


def box(draw, xy, title, body="", fill=PALE, outline="C8D6E2", title_color=NAVY, radius=18, title_size=20, body_size=17):
    x1, y1, x2, y2 = xy
    draw.rounded_rectangle(xy, radius=radius, fill=f"#{fill}", outline=f"#{outline}", width=3)
    tf, bf = font(title_size, True), font(body_size)
    title_lines = wrap_for_box(draw, title, tf, x2 - x1 - 32)
    y = y1 + 14
    for line in title_lines:
        bb = draw.textbbox((0, 0), line, font=tf)
        draw.text(((x1 + x2 - (bb[2] - bb[0])) / 2, y), line, font=tf, fill=f"#{title_color}")
        y += title_size + 5
    if body:
        y += 5
        for line in textwrap.wrap(body, width=max(18, int((x2-x1)/11))):
            bb = draw.textbbox((0, 0), line, font=bf)
            draw.text(((x1 + x2 - (bb[2] - bb[0])) / 2, y), line, font=bf, fill=f"#{INK}")
            y += body_size + 5


def arrow(draw, start, end, color=TEAL, width=5, label=None):
    draw.line((start, end), fill=f"#{color}", width=width)
    import math
    ang = math.atan2(end[1]-start[1], end[0]-start[0])
    size = 15
    p1 = (end[0] - size*math.cos(ang-0.45), end[1] - size*math.sin(ang-0.45))
    p2 = (end[0] - size*math.cos(ang+0.45), end[1] - size*math.sin(ang+0.45))
    draw.polygon([end, p1, p2], fill=f"#{color}")
    if label:
        mx, my = (start[0]+end[0])//2, (start[1]+end[1])//2
        draw.rounded_rectangle((mx-95, my-19, mx+95, my+17), radius=8, fill="white")
        draw.text((mx-90, my-16), label, font=font(15, True), fill=f"#{color}")


def build_diagrams(tmp: Path) -> dict[str, Path]:
    out = {}
    # Business lifecycle
    im, d = diagram_canvas("Ciclo de vida objetivo de un servicio Devioz", "Propuesta de negocio: adaptar puertas de control al tipo de contrato y riesgo.", height=560)
    labels = ["Oportunidad", "Descubrimiento", "Propuesta y contrato", "Planificación", "Construcción", "QA y aceptación", "Despliegue", "Operación y soporte", "Cierre y aprendizaje"]
    xs = [38 + i*160 for i in range(9)]
    for i, label in enumerate(labels):
        box(d, (xs[i], 250, xs[i]+132, 365), label, fill=MINT if i in (0, 8) else PALE, title_size=17, body_size=14)
        if i < len(labels)-1:
            arrow(d, (xs[i]+133, 307), (xs[i+1]-8, 307), width=4)
    d.text((70, 425), "Cada fase produce evidencia verificable, responsable, fecha objetivo y decisión de entrada/salida.", font=font(21, True), fill=f"#{NAVY}")
    out["lifecycle"] = save_img(im, tmp, "lifecycle")

    # Scenario/context view
    im, d = diagram_canvas("+1 Escenarios: actores y casos que validan la arquitectura", "El escenario conecta una necesidad de negocio con módulos, procesos y despliegue.")
    box(d, (60, 255, 330, 400), "Cliente", "Solicita alcance, revisa entregas y acepta hitos", fill="FFF4E8", outline="E9C7A7")
    box(d, (60, 485, 330, 630), "Desarrollador", "Planifica, registra avance, entrega evidencia", fill="EAF5EF", outline="BBD9C5")
    box(d, (585, 235, 920, 455), "Plataforma Devioz", "Clientes · contratos · proyectos · equipos · QA · releases · soporte", fill=MINT, outline=TEAL, title_size=24, body_size=20)
    box(d, (1180, 150, 1440, 290), "Admin / PM", "Prioriza, asigna y controla", fill="FFF4E8", outline="E9C7A7")
    box(d, (1180, 340, 1440, 480), "QA / Tech Lead", "Revisa calidad y riesgos", fill="F1EEFA", outline="CCC2E4")
    box(d, (1180, 530, 1440, 670), "Operaciones", "Despliega, observa y atiende", fill="E8F4F8", outline="BCD8E3")
    arrow(d, (330, 320), (585, 320), label="brief / aceptación")
    arrow(d, (330, 545), (585, 395), label="evidencias")
    arrow(d, (920, 280), (1180, 225), label="portafolio")
    arrow(d, (920, 340), (1180, 410), label="revisión")
    arrow(d, (920, 410), (1180, 595), label="release")
    out["scenario"] = save_img(im, tmp, "scenario")

    # Logical view
    im, d = diagram_canvas("Vista lógica: capacidades y límites de dominio", "Objetivo recomendado: monolito modular primero; separar módulos solo cuando exista razón medible.")
    box(d, (55, 210, 390, 360), "CRM y clientes", "Cuenta, contactos, oportunidad, contrato, SLA", fill="FFF4E8", outline="E9C7A7")
    box(d, (430, 210, 765, 360), "Delivery", "Portafolio, alcance, backlog, hitos, riesgos", fill=MINT, outline=TEAL)
    box(d, (805, 210, 1140, 360), "Equipo y colaboración", "Capacidad, asignaciones, mensajes, decisiones", fill="EAF5EF", outline="BBD9C5")
    box(d, (1180, 210, 1470, 360), "QA / Releases", "Evidencias, pruebas, aprobación, despliegue", fill="F1EEFA", outline="CCC2E4")
    box(d, (210, 465, 540, 615), "Finanzas y tiempo", "Presupuesto, horas, coste, margen", fill="FFF8E5", outline="E6D59B")
    box(d, (610, 465, 945, 615), "Experiencia", "Preferencias, notificaciones, comunidad", fill="E8F4F8", outline="BCD8E3")
    box(d, (1015, 465, 1350, 615), "Plataforma transversal", "Identidad, permisos, auditoría, archivos, integraciones", fill="F4F6F8", outline="C8D6E2")
    for x in [390,765,1140]: arrow(d, (x,285), (x+35,285), width=4)
    for start, end in [((260,360),(375,465)),((590,360),(770,465)),((965,360),(810,465)),((1320,360),(1190,465))]: arrow(d,start,end,width=3,color="8496A5")
    out["logical"] = save_img(im, tmp, "logical")

    # Process sequence
    im, d = diagram_canvas("Vista de procesos: entrega y aceptación de un hito", "Interacciones principales; las transacciones críticas deben ser atómicas e idempotentes.", height=920)
    lanes = [(50,"PM / Admin"),(355,"Dev"),(660,"Sistema"),(965,"QA"),(1270,"Cliente")]
    for x, name in lanes:
        d.rectangle((x,180,x+210,240),fill=f"#{NAVY}")
        d.text((x+18,197),name,font=font(20,True),fill="white")
        d.line((x+105,240,x+105,815),fill="#CAD5DF",width=3)
    centers = [155,460,765,1070,1375]
    steps = [
        (300,0,1,"Define alcance"),
        (380,1,2,"Entrega + evidencia"),
        (460,2,3,"En cola de QA"),
        (540,3,2,"Resultado QA"),
        (620,2,4,"Paquete al cliente"),
        (700,4,2,"Acepta / devuelve"),
        (780,2,0,"Actualiza y notifica"),
    ]
    for y, source, target, label in steps:
        arrow(d,(centers[source],y),(centers[target],y),color=TEAL if target>source else ORANGE,width=4,label=label)
    d.text((60,850),"Alternativas: rechazo → corrección → nueva versión; vencimiento → alerta; aceptación → siguiente hito según contrato.",font=font(18),fill=f"#{MUTED}")
    out["process"] = save_img(im, tmp, "process")

    # Development/package view
    im, d = diagram_canvas("Vista de desarrollo: organización del código", "Estado actual en PHP MVC y destino recomendado dentro del mismo repositorio.")
    tree = [
        (70,205,370,300,"public/ · front controller, assets"),(430,205,730,300,"bootstrap/ · rutas, inicio"),(790,205,1090,300,"app/Core · HTTP, auth, DB"),(1150,205,1450,300,"config/ · entorno"),
        (70,390,370,485,"app/Controllers · casos de uso"),(430,390,730,485,"app/Services · políticas y orquestación"),(790,390,1090,485,"app/Models · persistencia / dominio"),(1150,390,1450,485,"views/ · interfaz por rol"),
        (250,565,550,660,"database/ · schema + fixtures"),(650,565,950,660,"tests/ · unit, integration, E2E"),(1050,565,1350,660,"docs/ · ADR, 4+1, runbooks")]
    for a,b,c,e,t in tree: box(d,(a,b,c,e),t,fill=PALE,title_size=18,body_size=15)
    for a,b in [((370,250),(430,250)),((730,250),(790,250)),((1090,250),(1150,250)),((220,300),(220,390)),((580,300),(580,390)),((940,300),(940,390)),((1300,300),(1300,390)),((370,435),(430,435)),((730,435),(790,435)),((1090,435),(1150,435)),((400,485),(400,565)),((800,485),(800,565)),((1200,485),(1200,565))]: arrow(d,a,b,width=3,color="8A9AA8")
    out["development"] = save_img(im, tmp, "development")

    # Deployment view
    im, d = diagram_canvas("Vista física: topología objetivo por ambiente", "Separar desarrollo, pruebas y producción; el DocumentRoot debe apuntar únicamente a /public.")
    box(d,(45,260,280,390),"Usuarios", "Navegador web / móvil", fill="FFF4E8",outline="E9C7A7")
    box(d,(370,250,640,405),"Borde HTTPS", "DNS · TLS · WAF/rate limit (según hosting)",fill="E8F4F8",outline="BCD8E3")
    box(d,(735,225,1030,430),"Aplicación", "Apache + PHP 8.2+\nFront controller /public\nMonolito modular",fill=MINT,outline=TEAL)
    box(d,(1120,165,1440,310),"MySQL", "Backups cifrados\nRPO/RTO por definir",fill="FFF8E5",outline="E6D59B")
    box(d,(1120,370,1440,515),"Almacenamiento", "Entregas privadas\nretención / AV",fill="EAF5EF",outline="BBD9C5")
    box(d,(1120,570,1440,700),"Servicios externos", "Correo · OAuth · IA\nwebhooks limitados",fill="F1EEFA",outline="CCC2E4")
    arrow(d,(280,325),(370,325),label="HTTPS")
    arrow(d,(640,325),(735,325),label="HTTPS")
    arrow(d,(1030,270),(1120,240),label="PDO TLS")
    arrow(d,(1030,345),(1120,440),label="privado")
    arrow(d,(930,430),(1200,570),label="egress allowlist")
    out["physical"] = save_img(im, tmp, "physical")

    # State flow
    im, d = diagram_canvas("Estados de proyecto: distinguir aceptación, delivery y operación", "La tabla actual mezcla hitos académicos con estados corporativos y necesita un vocabulario validado.", height=620)
    status = [(60,260,"Draft"),(300,260,"Assigned"),(540,260,"In progress"),(790,260,"Pending review"),(1040,260,"In review"),(1280,260,"Approved")]
    for i,(x,y,label) in enumerate(status):
        box(d,(x,y,x+175,y+92),label,fill=MINT if i in (0,5) else PALE,title_size=18)
        if i<len(status)-1: arrow(d,(x+176,y+46),(status[i+1][0]-10,y+46),width=4)
    box(d,(360,450,560,540),"Rejected",fill="FCEEEE",outline="E7BABA",title_size=18)
    box(d,(950,450,1150,540),"Completed",fill="EAF5EF",outline="BBD9C5",title_size=18)
    arrow(d,(875,352),(535,450),color=ORANGE,label="QA devuelve")
    arrow(d,(1220,352),(1050,450),color=GREEN,label="aceptado")
    arrow(d,(460,450),(630,352),color=TEAL,label="nueva versión")
    d.text((60,570),"Propuesta: separar Project.status, Release.status y SupportCase.status; no llamar ‘En producción’ a ‘completed’ sin release real.",font=font(18,True),fill=f"#{NAVY}")
    out["states"] = save_img(im, tmp, "states")
    return out


def save_img(image: Image.Image, tmp: Path, name: str) -> Path:
    path = tmp / f"{name}.png"
    image.save(path, "PNG", optimize=True)
    return path


def make_styles() -> str:
    return f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
 <w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:color w:val="{INK}"/><w:sz w:val="20"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="100" w:line="276"/></w:pPr></w:pPrDefault></w:docDefaults>
 <w:style w:type="paragraph" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:color w:val="{INK}"/><w:sz w:val="20"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:pPr><w:spacing w:before="240" w:after="240"/></w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:color w:val="{NAVY}"/><w:sz w:val="42"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Subtitle"><w:name w:val="Subtitle"/><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:color w:val="{TEAL}"/><w:sz w:val="26"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="420" w:after="180"/><w:outlineLvl w:val="0"/></w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:color w:val="{NAVY}"/><w:sz w:val="31"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="260" w:after="120"/><w:outlineLvl w:val="1"/></w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:color w:val="{TEAL}"/><w:sz w:val="25"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/><w:next w:val="Normal"/><w:pPr><w:keepNext/><w:spacing w:before="170" w:after="80"/><w:outlineLvl w:val="2"/></w:pPr><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/><w:b/><w:color w:val="{INK}"/><w:sz w:val="22"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="Caption"><w:name w:val="caption"/><w:basedOn w:val="Normal"/><w:rPr><w:i/><w:color w:val="{MUTED}"/><w:sz w:val="18"/></w:rPr></w:style>
 <w:style w:type="paragraph" w:styleId="CodeBlock"><w:name w:val="Code Block"/><w:basedOn w:val="Normal"/><w:pPr><w:shd w:fill="F1F5F9"/><w:ind w:left="180"/></w:pPr><w:rPr><w:rFonts w:ascii="Consolas" w:hAnsi="Consolas"/><w:sz w:val="18"/></w:rPr></w:style>
</w:styles>'''


def cell_lines(*items: str) -> str:
    return "\n".join(items)


def author_doc(diagrams: dict[str, Path]) -> Doc:
    d = Doc()
    # Cover
    d.p("DEVIOZ", bold=True, color=TEAL, size=30, align="center", before=2200, after=350)
    d.p("ARQUITECTURA DE SOFTWARE", style="Title", align="center", after=120)
    d.p("Documento de arquitectura basado en el modelo 4+1", style="Subtitle", align="center", after=220)
    d.p("De una plataforma de prácticas a una plataforma end-to-end para una consultora de software", align="center", color=MUTED, size=24, after=700)
    d.p("Versión 1.0 · Documento de trabajo · 15 de octubre de 2026", align="center", bold=True, color=NAVY, size=22, after=260)
    d.p("Estado: propuesta para validar con dirección, delivery, desarrollo, QA, operaciones y clientes", align="center", color=ORANGE, size=20, after=700)
    d.p("Base inspeccionada: repositorio Devioz-Portafolio-de-Proyectos (PHP 8.2, MySQL, MVC propio)", align="center", color=MUTED, size=18, after=0)
    d.page_break()

    d.heading("Control del documento", 1)
    d.table(["Campo", "Contenido"], [
        ["Propósito", "Fijar una dirección de producto y arquitectura que permita discutir alcance, negocio, calidad, seguridad y evolución antes de seguir añadiendo pantallas."],
        ["Audiencia", "Dirección de Devioz; responsables de proyecto; clientes; desarrolladores; QA; operaciones; soporte; diseño de producto."],
        ["Naturaleza", "Documento de arquitectura propuesta. No representa un compromiso contractual ni asegura que todos los módulos estén implementados."],
        ["Método", "Modelo 4+1 de Philippe Kruchten: escenarios, vista lógica, procesos, desarrollo y despliegue físico."],
        ["Base de evidencia", "Inspección estática del código, rutas, vistas, modelos y schema.sql disponible en el repositorio."],
        ["Validación pendiente", "Entrevistas, talleres de proceso, seguridad, rendimiento, datos reales, regulación aplicable y restricciones del hosting."],
    ], [1800, 7560], 19)
    d.note("Convención de lectura", "[ACTUAL] existe en el código observado. [PROPUESTO] es una dirección recomendada. [DECISIÓN] requiere acuerdo de negocio. [RIESGO] exige mitigación. No confundir una tarjeta o etiqueta visual con una capacidad completa de producto.", TEAL)

    d.heading("Contenido", 1)
    toc = [
        "1. Resumen ejecutivo", "2. Visión y límites del producto", "3. Estado actual del sistema", "4. Actores y necesidades",
        "5. Lógica de negocio end-to-end", "6. Requisitos y casos de uso", "7. Dominio y modelo de información",
        "8. Arquitectura 4+1", "8.1 Escenarios (+1)", "8.2 Vista lógica", "8.3 Vista de procesos",
        "8.4 Vista de desarrollo", "8.5 Vista física", "9. Requisitos de calidad", "10. Experiencia de usuario y personalización",
        "11. Seguridad, privacidad y gobierno", "12. Integraciones y entrega de software", "13. Observabilidad y operación",
        "14. Brechas y decisiones de arquitectura", "15. Hoja de ruta recomendada", "16. Criterios de aceptación de la arquitectura",
        "17. Glosario", "18. Referencias y anexos"
    ]
    for item in toc:
        d.bullet(item)
    d.note("Uso en Word", "Los títulos están marcados como encabezados para facilitar el panel de navegación. Si se necesita un índice automático, insertar una tabla de contenido de Word usando los estilos Heading 1–3.", MUTED)

    d.heading("1. Resumen ejecutivo", 1)
    d.p("Devioz necesita coordinar el trabajo de una consultora desde la oportunidad comercial hasta la operación del software entregado. El sistema actual aporta un núcleo funcional para gestionar proyectos, responsables, hitos, entregas, revisiones, check-ins, conversaciones y exhibición pública. La estructura técnica actual es un MVC propio en PHP y MySQL, con páginas renderizadas en servidor y JavaScript para interacción.")
    d.p("La visión planteada requiere ampliar ese núcleo: la plataforma debe representar clientes y contactos, propuestas y contratos, proyectos y portafolios, equipos y capacidad, trabajo y costos, calidad y aceptación, releases, despliegues, incidentes, soporte, aprendizaje y comunidad. La información del cliente debe estar segregada de la comunidad interna y de la galería pública.")
    d.p("La recomendación principal es evolucionar de forma incremental hacia un monolito modular. No hay evidencia actual de volumen, equipos independientes o carga operativa que justifique microservicios. Primero se deben definir el ciclo del negocio, los estados, las reglas de autorización, las evidencias y los indicadores; después separar dominios y mejorar automatización. El modelo 4+1 ayuda a comprobar que cada escenario importante tiene soporte lógico, de proceso, de código y de despliegue.")
    d.table(["Conclusión", "Lectura de arquitectura"], [
        ["Lo que ya existe", "Gestión académica de proyectos y seguimiento del trabajo; no es todavía un PSA/ALM/ITSM completo para consultora."],
        ["Mayor decisión de producto", "Elegir si Devioz vende proyectos de precio fijo, tiempo y materiales, equipos dedicados, soporte recurrente o una mezcla. El modelo financiero y la aprobación cambian según el contrato."],
        ["Mayor riesgo de diseño", "Confundir proyecto completado o QA aprobado con despliegue productivo, aceptación del cliente y cierre comercial."],
        ["Dirección técnica", "Monolito modular, dominios con responsables, políticas centralizadas, API de integración, auditoría y despliegue controlado."],
        ["Primera entrega sugerida", "Mapa de proceso validado + permisos + entidad Cliente/Contacto + estado y criterios de salida del proyecto + trazabilidad."],
    ], [2600, 6760], 18)
    d.note("Decisión no negociable", "Una plataforma clara no debe ocultar problemas ni presionar al equipo con rachas, rankings o urgencias artificiales. Debe hacer visible el trabajo, reducir pasos innecesarios y facilitar pedir ayuda.", ORANGE)

    d.heading("2. Visión y límites del producto", 1)
    d.heading("2.1 Declaración de visión", 2)
    d.p("Crear una plataforma interna y de colaboración con clientes que dé a cada proyecto un único hilo de contexto: quién compra y decide, qué se prometió, quién lo construye, cómo se verifica, qué se publica, cómo se despliega, cómo se opera y qué aprendió el equipo. La experiencia se adapta por rol y preferencias sin crear versiones distintas de la verdad.")
    d.heading("2.2 Objetivos de producto", 2)
    for x in [
        "Reducir pérdida de contexto entre ventas, gestión, ingeniería, QA, operaciones y soporte.",
        "Convertir alcance y criterios de aceptación en elementos trazables y revisables.",
        "Ofrecer visibilidad confiable a clientes sin exponer discusiones, costos o datos internos.",
        "Prevenir asignación excesiva con señales de capacidad y compromisos explícitos.",
        "Registrar decisiones, riesgos, cambios de alcance, versiones y aprobaciones.",
        "Hacer de la comunidad un espacio de aprendizaje y reconocimiento voluntario, conectado pero separado de la evaluación laboral.",
        "Permitir mejoras incrementales sin comprometer la disponibilidad ni perder datos.",
    ]: d.bullet(x)
    d.heading("2.3 No objetivos de la primera etapa", 2)
    for x in [
        "No reemplazar contabilidad, nómina, CRM comercial especializado o repositorios Git desde el primer release.",
        "No construir microservicios por moda ni un editor de código dentro de la plataforma.",
        "No prometer despliegue automático universal; cada cliente y entorno debe tener política de aprobación y reversión.",
        "No convertir actividad diaria en vigilancia continua de teclado, cámara, ubicación o tiempo de presencia.",
        "No presentar puntuaciones de productividad individual como medida objetiva del valor de una persona.",
    ]: d.bullet(x)
    d.heading("2.4 Principios de producto", 2)
    d.table(["Principio", "Implicación"], [
        ["Una fuente de verdad", "Contrato, alcance aprobado, riesgos, entregas y releases enlazan a la misma identidad de cliente/proyecto."],
        ["Privacidad por defecto", "Lo interno no se publica al cliente ni a la galería por inferencia; cada audiencia tiene reglas explícitas."],
        ["Evidencia sobre apariencia", "Una insignia de aprobado requiere versión, criterio, responsable, fecha y registro de decisión."],
        ["Automatizar lo repetible", "Las transiciones generan notificaciones y tareas previsibles; las decisiones de alcance y producción conservan aprobación humana."],
        ["Diseñar para corregir", "Cambios reversibles, borrado lógico, historial de versiones, backups probados y rollback operativo."],
        ["Accesibilidad desde el inicio", "Navegación con teclado, contraste, etiquetas, foco visible y objetivos táctiles adecuados."],
    ], [2500, 6860], 18)

    d.heading("3. Estado actual del sistema", 1)
    d.p("La siguiente lectura se basa en los artefactos visibles del repositorio. No es una certificación de seguridad ni una prueba de funcionamiento. Las capacidades marcadas como actuales pueden tener flujos incompletos, errores de autorización o estados de muestra.")
    d.table(["Área", "Evidencia actual", "Lectura y brecha"], [
        ["Aplicación", "Front controller en public/index.php, Router propio, bootstrap/app.php; controladores y vistas PHP.", "MVC ligero; conviene conservar simplicidad y extraer casos de uso antes de crecer controladores."],
        ["Identidad", "Usuarios con roles admin/developer, login, perfil, OAuth declarado.", "Faltan roles y permisos de cliente, PM, QA, operaciones, finanzas y soporte; confirmar si OAuth funciona en todos los ambientes."],
        ["Proyectos", "Entidad Project con cliente como texto, estado ENUM, responsable, repo/live/video y galería.", "No existe entidad cliente ni contrato ni separación entre proyecto, release y soporte."],
        ["Entrega", "Milestones, deliveries, evaluations y project_files.", "Buen inicio para evidencia académica; requiere criterios, versiones, baseline de alcance, aceptación y vínculo con release."],
        ["Rituales", "Check-ins, comentarios, mensajes, reacciones y lounge.", "Separar comunicación de trabajo del feed social; definir retención, moderación y quién ve qué."],
        ["Galería", "Rutas públicas gallery/reels y portfolio.", "Debe publicar solo artefactos autorizados; retirar metadatos internos y aplicar consentimiento del cliente."],
        ["Datos", "MySQL con 10 tablas: users, projects, milestones, deliveries, evaluations, project_files, check_ins, comments, messages, audit_logs.", "No hay tablas dedicadas para clientes, contactos, contratos, oportunidades, releases, incidencias, SLA, costes o notificaciones persistentes independientes."],
        ["Infraestructura", "Apache/PHP, archivos bajo public/uploads, MySQL; configuración local y XAMPP.", "Almacenamiento web público para evidencias es un riesgo de confidencialidad; definir storage privado, backups, secretos y ambientes."],
        ["Calidad", "Checklist manual; PDO preparado, escape e(), CSRF y límites de roles en ciertas rutas.", "No se halló suite automatizada en los archivos observados; crear pruebas de autorización, estados y aceptación antes de ampliar."],
    ], [1500, 3400, 4460], 16)
    d.note("Aclaración", "Un README puede describir una intención, pero la fuente de verdad del diagnóstico son las rutas, el código y el esquema revisados. Las métricas promocionales de la landing no se consideran SLO medidos sin registros operativos.", ORANGE)

    d.heading("4. Actores y necesidades", 1)
    d.table(["Actor / persona", "Necesita", "Límites de visibilidad"], [
        ["Dirección / dueña de servicio", "Margen, cartera, riesgo, utilización, salud de clientes y decisiones que requieren atención.", "Acceso a portafolio empresarial; datos financieros solo según autorización."],
        ["Comercial / account manager", "Cuenta, contactos, oportunidad, propuesta, compromisos, renovaciones y riesgos de relación.", "No debe alterar evidencias técnicas o aprobar releases por defecto."],
        ["Project Manager / Delivery Lead", "Alcance, plan, capacidad, dependencias, riesgo, decisiones y aceptación.", "Control de su cartera; acceso interno amplio pero no secretos fuera de asignación."],
        ["Tech Lead / arquitecto", "Requisitos no funcionales, decisiones técnicas, dependencias, deuda y revisión de diseño.", "Acceso a contexto técnico de proyectos asignados."],
        ["Desarrollador", "Trabajo priorizado, criterios claros, bloqueo, feedback rápido, historial y carga razonable.", "Sus proyectos y colaboración; no ver presupuesto/margen sensible salvo permiso."],
        ["QA", "Criterios, build/version exacta, datos de prueba, defectos, evidencia y decisión auditable.", "Puede crear/veredicto de pruebas; no aprobar términos comerciales."],
        ["DevOps / operaciones", "Pipeline, artefactos, variables secretas, aprobaciones, observabilidad y rollback.", "Privilegios separados por ambiente; producción requiere MFA/aprobación."],
        ["Cliente (sponsor, PO, reviewer)", "Objetivos, estado, entregas, cambios, riesgos que le competen y aprobación clara.", "Solo datos de su organización y canales expresamente compartidos."],
        ["Soporte", "Incidentes, impacto, prioridad, SLA, diagnóstico, solución y comunicación.", "Datos mínimos necesarios; acceso temporal y auditado cuando hay información sensible."],
        ["Administrador de plataforma", "Identidad, configuración, seguridad, retención, integraciones y auditoría.", "Acceso privilegiado monitoreado; no equivale a acceso irrestricto por defecto."],
    ], [2200, 3800, 3360], 16)
    d.heading("4.1 Mapa de preocupaciones", 2)
    d.bullet("Negocio: rentabilidad, previsibilidad, satisfacción, renovación, exposición contractual y reputación.")
    d.bullet("Delivery: alcance verificable, dependencias, flujo de cambios, calidad, capacidad y fecha realista.")
    d.bullet("Personas: claridad, autonomía, carga sostenible, apoyo, reconocimiento justo y seguridad psicológica.")
    d.bullet("Tecnología: cambios seguros, datos consistentes, rendimiento, integridad, disponibilidad y recuperación.")
    d.bullet("Cliente: transparencia acordada, decisiones oportunas, protección de su información y aceptación sin ambigüedad.")

    d.heading("5. Lógica de negocio end-to-end", 1)
    d.p("El proyecto no debe iniciar en ‘asignar desarrollador’. La consultora necesita conservar la secuencia comercial y contractual que origina el trabajo, las políticas que lo gobiernan, y la evidencia que permite aceptar y operar lo entregado.")
    d.image(diagrams["lifecycle"], 7.15, "Figura 1. Flujo objetivo de oportunidad a aprendizaje operativo")
    d.heading("5.1 Flujo canónico propuesto", 2)
    stages = [
        ["1. Oportunidad", "Account registra organización, necesidad, sponsor, problema, fit, urgencia y fuente.", "Calificar / descartar / descubrir"],
        ["2. Descubrimiento", "Taller de objetivos, usuarios, sistemas, restricciones, datos, seguridad, dependencias y éxito.", "Brief validado; riesgos y supuestos visibles"],
        ["3. Propuesta y contrato", "Oferta, alcance, exclusiones, modelo de cobro, hitos, SLA, propiedad intelectual y aceptación.", "Aprobación comercial y legal; baseline versionado"],
        ["4. Planificación", "PM convierte baseline en épicas/hitos, responsables, capacidad, presupuesto, riesgos y releases tentativos.", "Plan de proyecto aprobado; equipo entiende su compromiso"],
        ["5. Construcción", "Backlog, trabajo, code review, decisiones técnicas, check-ins opcionales y gestión de impedimentos.", "Cambios ligados a requerimientos y commits/releases"],
        ["6. QA y aceptación", "Pruebas, defectos, accesibilidad, seguridad, evidencia y aceptación contractual del cliente.", "Puerta QA más aprobación cliente, ambas separadas"],
        ["7. Release y despliegue", "Artefacto inmutable, checklist, aprobador, ventana, migración, smoke tests y rollback.", "Registro de quién desplegó qué, cuándo, dónde y resultado"],
        ["8. Operación y soporte", "Monitoreo, incidentes, solicitudes, cambios urgentes, SLA y comunicación.", "Servicio estable o incidente asignado y escalado"],
        ["9. Cierre y aprendizaje", "Cierre financiero, retrospectiva, aceptación final, documentación, activos y lecciones reutilizables.", "Cierre explícito; soporte/garantía queda trazable"],
    ]
    d.table(["Fase", "Trabajo", "Salida / puerta"], stages, [1850, 4850, 2660], 15)
    d.heading("5.2 Roles de aprobación", 2)
    d.table(["Decisión", "Responsable", "Consultados", "Evidencia"], [
        ["Aceptar alcance y precio", "Account + dirección según umbral", "PM, Tech Lead, Finanzas", "Propuesta y versión de contrato"],
        ["Aceptar cambio de alcance", "Sponsor cliente + dueño comercial", "PM, Tech Lead, finanzas", "Change Request con costo/fecha/impacto"],
        ["Aprobar calidad", "QA Lead", "Desarrollador, Tech Lead", "Resultado de pruebas por versión"],
        ["Aceptar entrega contractual", "Cliente autorizado", "PM y QA", "Acta o aprobación digital con alcance"],
        ["Desplegar a producción", "Release Manager / Operaciones", "QA, cliente si el contrato lo exige", "Release record, plan y rollback"],
        ["Cerrar incidente", "Incident Owner", "Cliente, ingeniería, soporte", "Causa, solución, validación y comunicación"],
    ], [2400, 2150, 2400, 2410], 15)
    d.note("Segregación", "La persona que desarrolló un cambio puede preparar evidencia, pero no debería ser la única aprobación de QA ni de producción en servicios de alto riesgo. Definir excepciones para equipos pequeños y registrarlas.", ORANGE)

    d.heading("6. Requisitos y casos de uso", 1)
    d.heading("6.1 Casos de uso prioritarios", 2)
    d.table(["ID", "Caso", "Actor primario", "Resultado observable"], [
        ["UC-01", "Registrar cliente y contactos", "Account", "Cuenta con identificador, dueños, preferencias y clasificación de datos."],
        ["UC-02", "Calificar oportunidad", "Account", "Decisión con problema, valor, riesgo, probabilidad y siguiente paso."],
        ["UC-03", "Crear propuesta versionada", "Account + Tech Lead", "Alcance, exclusiones, precio, calendario y validez guardados como versión."],
        ["UC-04", "Convertir propuesta aprobada en proyecto", "PM", "Proyecto hereda cliente, contrato, baseline y responsables."],
        ["UC-05", "Planificar y asignar capacidad", "PM", "Asignación considera habilidades, disponibilidad y límites de carga."],
        ["UC-06", "Registrar requerimiento y criterio", "PM / PO", "Historia enlaza a hito, criterio de aceptación, prioridad y cambio de alcance."],
        ["UC-07", "Reportar avance o bloqueo", "Miembro de equipo", "Registro contextual, visible según rol; permite pedir ayuda sin castigo automático."],
        ["UC-08", "Entregar build o hito", "Desarrollador", "Versión, archivos privados, notas y checklist quedan asociados."],
        ["UC-09", "Revisar con QA", "QA", "Pruebas por build, defectos vinculados, resultado y revisor registrados."],
        ["UC-10", "Solicitar aceptación al cliente", "PM", "Cliente ve solo paquete aprobado y puede aceptar o devolver con comentarios."],
        ["UC-11", "Aprobar cambio de alcance", "Sponsor cliente", "Cambio reestima costo, fecha, riesgo y crea baseline nueva sin borrar historia."],
        ["UC-12", "Autorizar release", "Release Manager", "Release tiene artefacto, entorno, aprobaciones, pasos, health checks y reversa."],
        ["UC-13", "Reportar incidente", "Cliente / soporte", "Impacto, severidad, hora, servicio, responsable, SLA y comunicaciones."],
        ["UC-14", "Gestionar incidente", "Incident Owner", "Actualizaciones, mitigación, restauración y análisis posterior."],
        ["UC-15", "Publicar caso en portafolio", "Marketing + cliente", "Consentimiento, anonimización, assets aprobados y fecha de revisión."],
        ["UC-16", "Compartir experiencia interna", "Miembro", "Publicación moderable, audiencia clara y controles de notificación."],
        ["UC-17", "Ver salud de cartera", "Dirección", "Métricas por estado, margen y riesgo con definición del denominador."],
        ["UC-18", "Exportar datos de cuenta", "Privacidad / admin autorizado", "Exportación limitada, registrada y disponible para el titular autorizado."],
    ], [850, 2450, 1750, 4310], 15)
    d.heading("6.2 Requisitos funcionales de producto", 2)
    functional = [
        ("FR-01", "Gestión de cartera", "Crear, versionar, archivar y buscar clientes, contactos, oportunidades, propuestas, contratos y proyectos con historial."),
        ("FR-02", "Multiempresa", "Un usuario cliente pertenece a una o más organizaciones con membresías, rol, estado y acceso revocable."),
        ("FR-03", "Contratos", "Modelar tipo de servicio, precio, moneda, impuestos, facturación, vigencia, renovación, SLA, garantía y anexos."),
        ("FR-04", "Alcance", "Guardar baseline aprobado y Change Requests; mostrar deltas de costo, fecha, riesgo y criterios."),
        ("FR-05", "Trabajo", "Planificar hitos/tareas, dependencias, estimación, prioridad, bloqueo, owner y criterios de terminado."),
        ("FR-06", "Capacidad", "Registrar asignación por semana/proyecto y alertar conflicto; no prometer precisión sin fuente de disponibilidad."),
        ("FR-07", "Calidad", "Versionar criterios, planes y resultados de prueba; defectos con severidad, evidencia y build afectado."),
        ("FR-08", "Entrega", "Crear paquetes de entrega firmados por versión de artefacto y cadena de aceptación."),
        ("FR-09", "Release", "Promover el mismo artefacto por ambientes, conservar manifest y permitir rollback documentado."),
        ("FR-10", "Operación", "Incidentes, solicitudes, SLA, mantenimientos, postmortems y acuerdos de soporte."),
        ("FR-11", "Comunicación", "Mensajes/contexto vinculados a tarea, hito, cuenta o incidente; evitar feeds sin contexto."),
        ("FR-12", "Comunidad", "Espacios opt-in para compartir aprendizajes, reconocer contribuciones y moderar contenido."),
        ("FR-13", "Auditoría", "Registrar actor, acción, recurso, fecha, resultado y correlación; proteger el log contra edición normal."),
        ("FR-14", "Personalización", "Preferencias de densidad, tema, zona horaria, notificaciones y widgets dentro de límites de accesibilidad."),
        ("FR-15", "Reportes", "Mostrar cartera, flujo, calidad, soporte y costos con fórmulas, filtros, permisos y fecha de corte."),
    ]
    d.table(["ID", "Capacidad", "Requisito"], [[a,b,c] for a,b,c in functional], [950,2050,6360], 15)
    d.heading("6.3 Reglas de negocio candidatas", 2)
    rules = [
        ["BR-01", "Un proyecto comercial solo se crea desde oportunidad/propuesta aprobada o con excepción justificada."],
        ["BR-02", "Toda entrega está asociada a un proyecto y a una versión de alcance; una evaluación referencia exactamente una versión."],
        ["BR-03", "La aprobación de QA no sustituye la aceptación contractual del cliente."],
        ["BR-04", "Un cambio posterior a baseline crea Change Request; no sobrescribe el alcance firmado."],
        ["BR-05", "Un release solo se registra si existe artefacto identificable, destino, aprobador, resultado y ruta de rollback o excepción documentada."],
        ["BR-06", "Solo los miembros habilitados de una organización cliente pueden ver elementos compartidos con esa organización."],
        ["BR-07", "Un incidente crítico abre un canal operativo con responsable y política de comunicación; no depende de publicaciones sociales."],
        ["BR-08", "No se usa el número de check-ins, mensajes o commits como proxy aislado de productividad individual."],
        ["BR-09", "La galería es publicación explícita, revocable según derechos y aprobada por el dueño del cliente."],
        ["BR-10", "Los cambios de estado inválidos se rechazan en servidor y quedan explicados al usuario."],
        ["BR-11", "Un usuario desactivado pierde sesiones y tokens; sus registros históricos conservan autoría salvo obligación de anonimización."],
        ["BR-12", "Cada métrica ejecutiva publica definición, periodo, población, fuente y limitación."],
    ]
    d.table(["ID", "Regla propuesta"], rules, [1000,8360], 17)

    d.heading("7. Dominio y modelo de información", 1)
    d.heading("7.1 Contextos delimitados", 2)
    d.table(["Contexto", "Responsabilidad", "Entidades centrales propuestas"], [
        ["CRM", "Relación y venta", "Organization, Contact, Opportunity, Activity"],
        ["Commercial", "Oferta y compromiso", "Proposal, Contract, ContractVersion, PriceItem, ChangeRequest"],
        ["Portfolio / Delivery", "Plan y ejecución", "Project, ScopeBaseline, Requirement, Milestone, WorkItem, Risk, Decision"],
        ["People / Capacity", "Equipos y disponibilidad", "User, Team, Skill, Assignment, CapacitySnapshot"],
        ["Quality", "Verificación y aceptación", "TestPlan, TestRun, Defect, Evidence, CustomerAcceptance"],
        ["Release / Operations", "Promoción y soporte", "Artifact, Release, Deployment, Environment, Incident, ServiceRequest, SLA"],
        ["Collaboration", "Comunicación y aprendizaje", "Conversation, Message, CommunityPost, Reaction, ModerationAction"],
        ["Platform", "Controles transversales", "OrganizationMembership, Role, Permission, AuditEvent, Notification, FileAsset"],
        ["Finance (integrable)", "Coste y cobro", "TimeEntry, Budget, InvoiceReference, CostRate, MarginSnapshot"],
    ], [2000,3400,3960], 16)
    d.heading("7.2 Diagrama lógico de dominios", 2)
    d.image(diagrams["logical"], 7.2, "Figura 2. Contextos lógicos objetivo; sus límites deben validarse con responsables de negocio")
    d.heading("7.3 Modelo de datos: actual frente a objetivo", 2)
    d.table(["Objeto", "Estado en DB", "Decisión de evolución"], [
        ["User", "Existe; roles enum admin/developer.", "Separar identidad de membresía por organización; modelar rol por alcance, MFA y estado de sesión."],
        ["Project", "Existe; client_name como texto; estados en enum.", "Referenciar organization_id, contract_id y portfolio; eliminar ambigüedad del nombre del cliente."],
        ["Milestone / Delivery / Evaluation", "Existen; ligadas al proyecto.", "Añadir versiones, criterios trazables, revisión por build y aceptación distinta de QA."],
        ["ProjectFile", "Existe; ruta pública bajo uploads en la configuración observada.", "FileAsset privado, autorización de descarga, antivirus, retención y checksum."],
        ["Release / Deployment", "No se observa tabla dedicada.", "Crear entidades independientes; deployment es una ejecución de release sobre environment."],
        ["Incident / SLA", "No se observa tabla dedicada.", "Modelar ticket, impacto, severidad, reloj SLA, comunicación y postmortem."],
        ["Organization / Contract", "No hay tablas dedicadas observadas.", "Introducir primero tenant y membresía; contrato versionado con acceso y vencimientos."],
    ], [1950,3100,4310], 16)
    d.heading("7.4 Integridad y trazabilidad", 2)
    for x in [
        "Usar identificadores estables (UUID/ULID si se requiere exposición externa) y claves foráneas con borrado explícito; evitar cascadas sobre registros legales o auditoría.",
        "Guardar eventos de negocio inmutables o append-only para aprobación, aceptación, release, cambio contractual y acceso privilegiado.",
        "Separar tiempo UTC almacenado de zona horaria de presentación; fijar America/Lima como preferencia inicial, no como supuesto universal para clientes.",
        "Identificar por separado fecha comprometida, fecha estimada vigente, fecha real y fecha de aprobación; no sobreescribir previsiones anteriores.",
        "Versionar documentos y artefactos por hash, tamaño, tipo detectado, uploader, clasificación, retención y relación con el alcance.",
    ]: d.bullet(x)

    d.heading("8. Arquitectura 4+1", 1)
    d.p("El modelo 4+1 presenta vistas concurrentes para responder preguntas distintas. La vista de escenarios valida que las otras cuatro cuentan una historia coherente. No son cinco diagramas decorativos ni una receta para escoger framework: son perspectivas de arquitectura para stakeholders distintos. [1]")
    d.table(["Vista", "Pregunta", "Audiencia", "Diagramas de este documento"], [
        ["+1 Escenarios", "¿Qué hace el sistema en situaciones importantes?", "Cliente, negocio, equipo, arquitectura", "Contexto de actores; entrega de hito; aceptación y despliegue"],
        ["Lógica", "¿Qué conceptos y responsabilidades resuelven el problema?", "Product, dominio, ingeniería", "Contextos de negocio; entidades y límites"],
        ["Procesos", "¿Cómo colaboran los componentes en tiempo de ejecución?", "Backend, QA, seguridad, operación", "Secuencia de entrega, notificaciones, jobs e incidentes"],
        ["Desarrollo", "¿Cómo se organiza y construye el software?", "Desarrolladores, QA, build/release", "Paquetes actuales/objetivo; dependencias y módulos"],
        ["Física", "¿Dónde corre cada parte y cómo se conecta?", "Operaciones, hosting, seguridad", "Entornos, almacenamiento, base de datos y proveedores"],
    ], [1750,2850,2050,2710], 16)

    d.heading("8.1 +1 — Escenarios", 2)
    d.p("Los escenarios son pruebas de consistencia arquitectónica: para cada uno se identifica actor, precondición, flujo normal, alternativas, datos, controles, resultado y módulos participantes.")
    d.image(diagrams["scenario"], 7.15, "Figura 3. Escenarios, actores y capacidades que deben cruzar las vistas")
    d.heading("Escenario S1 — Cliente aprueba una entrega", 3)
    for item in [
        "Precondición: organización, miembros, contrato, baseline y criterios están definidos; el usuario cliente está autorizado.",
        "PM reúne versión del entregable, release notes, evidencia QA, riesgos abiertos y criterios pactados.",
        "Sistema crea una solicitud de aceptación con fecha límite y registra la versión exacta compartida.",
        "Cliente revisa, acepta con identidad/fecha o rechaza con comentario ligado a criterio.",
        "El sistema persiste una decisión auditable, notifica a delivery y no altera la evidencia anterior.",
        "Excepción: si el cliente propone trabajo fuera del baseline, se convierte en Change Request y no bloquea silenciosamente el contrato actual.",
    ]: d.numbered("S1", item)
    d.heading("Escenario S2 — Dev entrega un hito bloqueado por defecto", 3)
    d.p("El dev envía evidencia y build con una prueba fallida; QA marca defecto bloqueante, lo asocia al requisito y devuelve la entrega. El sistema conserva la versión revisada, comunica causa accionable, reabre trabajo vinculado y evita sumar el hito al avance aprobado. La nueva entrega crea versión nueva sin borrar la anterior.")
    d.heading("Escenario S3 — Release productivo con rollback", 3)
    d.p("Release Manager selecciona artefacto inmutable ya aprobado, destino productivo, ventana y pasos. Sistema verifica permisos, aprobaciones, backup/migración y health checks; registra tiempos y resultado. Si el chequeo falla, se ejecuta rollback aprobado y se abre o enlaza incidente. El cliente recibe comunicación según contrato.")
    d.heading("Escenario S4 — Proyecto cambia de alcance", 3)
    d.p("Sponsor solicita función no incluida. PM registra el cambio, Tech Lead estima impactos técnicos y de seguridad, Account/Finanzas reestima costo y fecha. Hasta que las partes autorizadas acepten una nueva versión contractual, el trabajo sigue contra el baseline vigente y el cambio queda pendiente o rechazado.")
    d.heading("Escenario S5 — Incidente crítico fuera de horario", 3)
    d.p("Alerta crea incidente con servicio afectado, severidad inicial, reloj SLA y responsable. Operaciones mitiga, publica actualizaciones para el público autorizado, restaura servicio y documenta causa. El postmortem se centra en sistemas y procesos, con acciones dueñas y fechas; no se usa para buscar culpables.")
    d.heading("Escenario S6 — Publicar caso de éxito", 3)
    d.p("Marketing solicita publicación, valida permiso del cliente y titularidad de assets, elimina secretos y datos personales, recibe aprobación de cliente y proyecto, publica un conjunto limitado de evidencias y conserva forma de retirar o actualizar.")

    d.heading("8.2 Vista lógica", 2)
    d.p("La vista lógica separa capacidades de negocio y sus invariantes. En la primera etapa, cada contexto puede ser un módulo PHP dentro del monolito. Se evita que un módulo modifique tablas de otro por acceso ad hoc; se definen servicios/casos de uso y contratos internos.")
    d.table(["Módulo", "Responsabilidad", "Invariantes de dominio"], [
        ["Identity & Access", "Login, membresía, autorización, sesiones, MFA, invitaciones.", "Ninguna solicitud cruza tenant sin política explícita; acceso revocable."],
        ["CRM", "Organización, contactos, oportunidad y actividades.", "Contacto pertenece a organización; opt-out y preferencia se respetan."],
        ["Commercial", "Propuestas, contratos, baseline y cambios.", "Versión firmada no se sobrescribe; Change Request requiere aprobación."],
        ["Portfolio / Delivery", "Proyecto, requisitos, hitos, tareas, riesgo y decisiones.", "Avance deriva de evidencia/estado con fórmula definida."],
        ["Quality", "Test plans, ejecuciones, defectos y aceptación técnica.", "QA relaciona su resultado con build y criterios exactos."],
        ["Release / Ops", "Artefactos, releases, environments, deployments e incidentes.", "Desplegar solo artefacto aprobado en entorno autorizado; rollback rastreable."],
        ["People / Capacity", "Habilidades, equipos, asignación y carga.", "Carga no excede umbrales sin excepción visible; no diagnostica rendimiento humano."],
        ["Collaboration", "Conversación contextual, notificaciones, comunidad y moderación.", "Audiencias explícitas; comunicación de incidentes no depende del feed social."],
        ["File & Audit", "Adjuntos privados, retención y auditoría transversal.", "Descarga autorizada; bitácora de cambios críticos no editable por usuario normal."],
    ], [1900,3700,3760], 15)
    d.heading("8.2.1 Entidades y relaciones principales", 3)
    d.code("Organization 1..* Membership *..1 User\nOrganization 1..* Contact\nOrganization 1..* Contract 1..* ContractVersion\nContract 1..* Project 1..* ScopeBaseline\nProject 1..* Requirement 1..* WorkItem / Milestone\nWorkItem 1..* Evidence; Evidence *..1 ArtifactVersion\nArtifactVersion 1..* TestRun; TestRun 1..* Defect\nArtifactVersion 1..* Release 1..* Deployment *..1 Environment\nProject 1..* Incident; Incident *..1 SLA / SupportPlan\nAll critical changes -> AuditEvent(actor, action, object, timestamp, correlationId)")
    d.note("Diseño inicial", "No hace falta implementar Event Sourcing completo para obtener historial. Una tabla de eventos/auditoría append-only para hechos críticos y tablas actuales para consultas suele ser una transición más sencilla.", MUTED)

    d.heading("8.3 Vista de procesos", 2)
    d.p("La vista de procesos describe llamadas sincrónicas, trabajo asíncrono, concurrencia, aislamiento transaccional, fallos y reintentos. En PHP clásico cada request puede ser aislado, pero notificaciones, antivirus, generación de reportes y webhooks no deben prolongar la petición web.")
    d.image(diagrams["process"], 7.2, "Figura 4. Intercambio de entrega, QA y aceptación")
    d.heading("8.3.1 Secuencia objetivo para entrega", 3)
    d.code("Browser -> Router: POST /projects/{id}/deliveries + CSRF\nRouter -> AuthZ: verificar sesión, membership, rol y proyecto\nDeliveryService -> DB (transaction): validar estado, crear DeliveryVersion\nDeliveryService -> FileStore: guardar objeto privado / calcular checksum\nDeliveryService -> Outbox: registrar DeliverySubmitted en misma transacción\nOutboxWorker -> Notification: avisar QA/PM con enlace autorizado\nQA -> API: registrar TestRun por artifactVersion\nCustomerPortal -> API: aceptar o devolver baseline/criterios mostrados\nAudit -> DB: actor, decisión, versión, timestamp, correlación")
    d.heading("8.3.2 Concurrencia y confiabilidad", 3)
    d.table(["Riesgo de proceso", "Control recomendado"], [
        ["Doble envío por doble clic/reintento", "Idempotency key por usuario y operación; constraint de DB y respuesta repetible."],
        ["Dos revisores cambian estado a la vez", "Control de versión optimista (updated_at/version) y conflicto visible."],
        ["Archivo guardado pero DB falla", "Staging temporal + commit de metadatos + limpieza de huérfanos; reconciliación periódica."],
        ["DB confirma pero notificación falla", "Outbox transaccional con worker/reintentos y DLQ simple."],
        ["Usuario pierde sesión", "401/419 JSON consistente; conservar borrador no sensible y permitir reautenticación."],
        ["Integración externa lenta", "Timeout, retry acotado con jitter, circuit breaker si aporta valor; request id y fallback."],
        ["Carga alta de reportes", "Paginación, límites, índices medidos, export asíncrono y límites de frecuencia."],
    ], [2850,6510], 16)
    d.heading("8.3.3 Máquina de estados", 3)
    d.p("La implementación actual usa estados de proyecto como assigned, draft, in_progress, pending_review, in_review, approved, rejected, completed y archived. La documentación objetivo debe separar tres máquinas: (a) ciclo de proyecto/servicio; (b) revisión de entrega y aceptación; (c) release/deployment. ‘completed’ no equivale a ‘en producción’, y ‘approved’ no significa que el cliente haya aceptado el contrato.")
    d.image(diagrams["states"], 7.15, "Figura 5. Separación recomendada de estados y decisiones")

    d.heading("8.4 Vista de desarrollo", 2)
    d.p("La vista de desarrollo organiza módulos para que el equipo pueda construir, revisar, probar y desplegar. El repositorio actual contiene un MVC propio y ficheros PHP sencillos; la propuesta respeta ese punto de partida y define límites antes de extraer servicios externos.")
    d.image(diagrams["development"], 7.15, "Figura 6. Organización de código objetivo compatible con la base PHP actual")
    d.table(["Capa / paquete", "Regla de dependencia"], [
        ["HTTP / Router / Controllers", "Convierte requests en commands/queries; no decide reglas financieras ni transición compleja."],
        ["Application services", "Orquesta casos de uso, autorización contextual y transacciones."],
        ["Domain", "Entidades, políticas, invariantes y eventos de negocio sin dependencia de vistas."],
        ["Infrastructure", "PDO/repositories, file store, correo, OAuth, proveedor de IA y reloj."],
        ["Views / Frontend", "Presentación accesible; consume contratos documentados; no contiene decisiones de permisos."],
        ["Tests", "Unitarios de reglas; integración real de repositorios; feature por rol; E2E por escenarios +1."],
        ["Docs / ADR", "Decisiones con contexto, opciones, trade-offs, consecuencia y fecha de revisión."],
    ], [2500,6860], 16)
    d.heading("8.4.1 Estrategia técnica", 3)
    for x in [
        "Conservar PHP 8.2+ si el hosting lo requiere, pero fijar versiones soportadas y extensiones en una matriz reproducible.",
        "Adoptar Composer lock reproducible, formato de código, análisis estático/lint y CI antes de introducir grandes refactors.",
        "Mover lógica de negocio desde controladores hacia servicios por caso de uso sin reescribir el MVC de golpe.",
        "Versionar SQL como migraciones incrementales; schema.sql puede conservarse como bootstrap, no como mecanismo único de evolución productiva.",
        "Definir interfaces para FileStore, Mailer, OAuthProvider y AIProvider; no acoplar reglas del proyecto a un proveedor concreto.",
        "Mantener API propia con versionado y contratos; generar OpenAPI para integraciones cuando el primer consumidor real lo requiera.",
    ]: d.bullet(x)

    d.heading("8.5 Vista física", 2)
    d.p("La vista física muestra nodos de ejecución, redes y límites de confianza. El repositorio documenta XAMPP y hosting compartido; eso puede servir para demostración, pero no debe asumirse automáticamente como plataforma adecuada para clientes, despliegues críticos, secretos y evidencias confidenciales.")
    d.image(diagrams["physical"], 7.15, "Figura 7. Topología objetivo y fronteras de confianza")
    d.heading("8.5.1 Ambientes mínimos", 3)
    d.table(["Ambiente", "Uso", "Datos y controles"], [
        ["Local", "Desarrollo individual", "Datos sintéticos; secretos locales ignorados por Git; APP_DEBUG permitido solo local."],
        ["CI", "Lint, análisis, unit tests e integración", "DB efímera; credenciales de mínimo privilegio; artefactos retenidos por plazo."],
        ["Staging", "Validación integrada y aceptación de cliente", "Datos anonimizados; configuración similar a producción; no copiar PII sin base legal."],
        ["Production", "Servicio real", "HTTPS, debug apagado, secretos fuera del repo, backups, alertas, acceso break-glass auditado."],
    ], [1600,2700,5060], 16)
    d.heading("8.5.2 Ubicación de datos y archivos", 3)
    for x in [
        "La raíz web es /public; app, config, .env, logs, backups y archivos privados quedan fuera del DocumentRoot.",
        "Los entregables de código, contratos y evidencias se guardan en almacenamiento privado con URLs temporales y autorización; no en una ruta pública predecible.",
        "MySQL acepta conexiones solo desde la aplicación; backups cifrados se almacenan en ubicación distinta al host de producción.",
        "Correo, OAuth, IA y webhooks salen por HTTPS con host allowlist, timeout, límites y redacción de datos sensibles.",
        "Definir RPO/RTO por nivel de servicio con dirección y clientes; probar restauración, no solo crear copias.",
    ]: d.bullet(x)

    d.heading("9. Requisitos de calidad", 1)
    d.p("Los valores siguientes son objetivos iniciales para debatir. No son métricas actuales ni SLO aprobados. Antes de prometerlos, medir carga representativa y confirmar presupuesto/hosting.")
    d.table(["Atributo", "Objetivo inicial propuesto", "Cómo comprobar"], [
        ["Disponibilidad", "99.5% mensual para portal interno; disponibilidad contractual separada por cliente.", "Monitor externo, ventana acordada y exclusiones escritas."],
        ["Rendimiento", "p95 < 2 s en listas principales con datos paginados; p95 API < 800 ms sin proveedor externo.", "APM/medición con conjunto de datos realista; separar tiempo externo."],
        ["Carga de archivos", "Rechazo anticipado por tamaño/tipo; carga reanudable para archivos grandes en fase posterior.", "Casos de límite, MIME real y escenarios de red interrumpida."],
        ["Seguridad", "Ningún acceso cross-tenant en pruebas; MFA para roles privilegiados; secretos fuera del repo.", "Suite de autorización, revisión SAST/DAST y auditoría de privilegios."],
        ["Accesibilidad", "WCAG 2.2 AA como objetivo para flujos críticos; teclado y lector de pantalla en login, proyectos, entrega y portal cliente.", "Auditoría automática más revisión manual con personas usuarias."],
        ["Recuperación", "RPO ≤ 24 h inicial y RTO ≤ 8 h solo como hipótesis a validar.", "Restauración periódica cronometrada y registro de resultado."],
        ["Mantenibilidad", "Módulos con responsabilidades claras; transiciones de estado cubiertas por pruebas; cambios seguros y revisables.", "Cobertura por reglas críticas, arquitectura y análisis de dependencias."],
        ["Privacidad", "Minimización, retención por categoría, exportación y borrado/anonimización con política aprobada.", "Inventario de datos, revisión legal y simulacro de solicitud."],
        ["Compatibilidad", "Chrome/Edge/Firefox actuales y viewport móvil para los flujos principales.", "Matriz de navegador y resolución publicada por release."],
    ], [1700,4500,3160], 15)
    d.note("No convertir objetivos en publicidad", "Los SLO propuestos necesitan responsable, método de cálculo, periodo, presupuesto de error, exclusiones y mecanismo de comunicación. Un ‘99.9%’ en una tarjeta sin medición no es evidencia de disponibilidad.", ORANGE)

    d.heading("10. Experiencia de usuario y personalización", 1)
    d.p("La meta ‘amigable y no aburrida’ se traduce en decisiones de interfaz observables: reducir búsqueda y memoria, revelar complejidad por etapas, confirmar acciones sensibles, mantener consistencia y hacer la carga de trabajo visible sin castigar a quien reporta un problema. La documentación no atribuye estos efectos a regiones cerebrales ni promete resultados clínicos.")
    d.heading("10.1 Diseño basado en evidencia y validación", 2)
    d.p("La teoría de carga cognitiva sugiere que la presentación de información puede influir en recursos mentales disponibles para aprender y resolver tareas; su aplicación aquí es una hipótesis de diseño que debe evaluarse con tareas reales, no una garantía de productividad. [2] La accesibilidad se plantea con WCAG 2.2 como referencia normativa vigente y con verificación manual; cumplir una lista automática no garantiza por sí solo una experiencia accesible. [3]")
    d.table(["Principio de diseño", "Aplicación en Devioz", "Medida"], [
        ["Reducir carga extrínseca", "Tableros muestran lo necesario al rol, filtro persistente, lenguaje directo, evitar duplicar el mismo dato.", "Tiempo para encontrar siguiente acción; errores de interpretación."],
        ["Progreso significativo", "Mostrar avance por criterios aceptados, dependencias y bloqueos; evitar progreso cosmético.", "Concordancia entre progreso visible y evidencia aprobada."],
        ["Autonomía", "Permitir elegir notificaciones, densidad, tema, orden de widgets y foco semanal.", "Tareas completadas sin asistencia y valoración de control."],
        ["Feedback respetuoso", "Confirmación inmediata, undo cuando sea viable, causa legible y siguiente paso.", "Tasa de error recuperado; abandono de formulario."],
        ["Seguridad psicológica", "Bloqueo o estimación cambiante genera ayuda y conversación, no ranking punitivo.", "Encuestas voluntarias; tiempo de resolución de bloqueos."],
        ["Reconocimiento con contexto", "Agradecer colaboración/mentoría y aprendizajes de equipo con opt-in; no premiar horas online.", "Participación cualitativa, percepción de justicia y moderación."],
        ["Accesibilidad", "Contraste, foco, labels, errores asociados, movimiento reducido y objetivos grandes.", "Pruebas WCAG y sesiones con diversidad de capacidades."],
    ], [2000,4660,2700], 15)
    d.heading("10.2 Personalización segura", 2)
    for x in [
        "Preferencias por usuario: tema, idioma, zona horaria, densidad, landing inicial, horario silencioso y canales de notificación.",
        "Preferencias no deben cambiar permisos, retención, auditoría, cálculos comerciales ni advertencias críticas.",
        "No usar color como único estado; incluir texto/icono y contraste verificable. Soportar zoom, foco visible, teclado y reduced motion.",
        "La personalización usa datos declarados y configurables; evitar inferir salud, ánimo, atención o rendimiento a partir de actividad.",
        "Las notificaciones urgentes se reservan para incidentes y compromisos; agrupación y silencio para señales de baja prioridad.",
    ]: d.bullet(x)
    d.heading("10.3 Experimentos de experiencia", 2)
    d.p("Probar prototipos con clientes, PM, desarrolladores y QA. Medir éxito de tareas, comprensión, esfuerzo percibido, accesibilidad, falsas alarmas y errores. Cambiar una variable a la vez cuando se compare. No usar experimentos que oculten información contractual o reduzcan controles de seguridad.")

    d.heading("11. Seguridad, privacidad y gobierno", 1)
    d.heading("11.1 Límites de confianza", 2)
    d.table(["Zona", "Datos", "Controles base"], [
        ["Pública", "Galería, landing y contenidos con autorización.", "Allowlist de publicación, escape contextual, rate limit, CSP y consentimiento revocable."],
        ["Usuario autenticado", "Perfil y datos personales propios.", "Sesión segura, CSRF, reautenticación para operaciones sensibles."],
        ["Organización cliente", "Contratos, proyectos y entregas compartidas.", "Membership + policy por recurso/tenant; test negativo de aislamiento."],
        ["Interna Devioz", "Costos, notas internas, discusiones y riesgos.", "Prohibir exposición accidental a portal cliente; clasificar campos y audiencias."],
        ["Privilegiada", "Secretos, producción, auditoría, exportaciones.", "MFA, menor privilegio, just-in-time, alertas y bitácora append-only."],
    ], [1900,3600,3860], 16)
    d.heading("11.2 Amenazas que deben modelarse", 2)
    for x in [
        "Acceso cross-tenant por IDOR: probar rutas con IDs de otra organización y revisar exportaciones, archivos y previews.",
        "XSS almacenado en nombre, descripción, comentarios, video_url, avatar, markdown, stack tecnológico o metadatos.",
        "CSRF en todas las mutaciones; cada endpoint JSON tiene token y exige auth según audiencia. Excepciones deben documentarse y probarse.",
        "Abuso de archivos: ZIP bomb, malware, polyglot, MIME falso, path traversal, descarga pública y agotamiento de almacenamiento.",
        "Abuso de integración o SSRF: preview-link solo admite http/https y bloquea loopback, link-local, redes privadas, metadata IP y redirecciones no verificadas.",
        "Enumeración y abuso de login: límites por cuenta/IP, mensajes consistentes, MFA y monitoreo sin registrar contraseñas/tokens.",
        "Exposición de secretos por logs, archivos de configuración, fixtures, capturas o historial Git; rotación inmediata si se publicaron.",
        "Ataque a pipeline/release: artefacto sustituido, secret leakage, dependencia comprometida o aprobación falsa.",
    ]: d.bullet(x)
    d.heading("11.3 Políticas requeridas", 2)
    d.table(["Política", "Mínimo a definir"], [
        ["Clasificación de datos", "Público, interno, confidencial cliente, personal, secreto; dueño y tratamiento por clase."],
        ["Retención", "Logs, contratos, check-ins, mensajes, archivos, backups y evidencia; retención legal acordada."],
        ["Acceso", "Matriz rol × acción × recurso, tenant, herencia, expiración y acceso de emergencia."],
        ["Incidentes", "Canal de reporte, severidad, escalamiento, comunicación, preservación de evidencia y postmortem."],
        ["Privacidad", "Inventario, finalidad, base legal, minimización, derechos de titulares y respuesta a brechas según jurisdicción."],
        ["Desarrollo seguro", "Code review, dependencias fijadas, secretos, análisis, release gates y gestión de vulnerabilidades."],
    ], [2200,7160], 16)
    d.note("Jurisdicción", "La empresa debe identificar con asesoría competente qué leyes de protección de datos, laborales, contractuales y fiscales aplican en los países donde opera y atiende clientes. Este documento no sustituye una opinión legal.", ORANGE)

    d.heading("12. Integraciones y entrega de software", 1)
    d.heading("12.1 Integraciones por fases", 2)
    d.table(["Integración", "Valor", "Política"], [
        ["GitHub/GitLab", "Issues, PRs, commit y releases enlazados a requisitos.", "OAuth mínimos scopes; webhooks firmados; evitar exponer repositorios privados a clientes por defecto."],
        ["Correo/calendario", "Invitaciones, recordatorios y coordinación.", "Consentimiento, plantilla segura, preferencia horario y límite anti-spam."],
        ["CI/CD", "Estado de build, test, artifact y despliegue.", "Webhook firmado, artifact digest y credenciales por ambiente."],
        ["Observabilidad", "Errores, métricas y uptime del sistema entregado.", "No enviar PII/secretos; tenant y retención configurados."],
        ["Facturación", "Referencia de hitos/consumo a sistema financiero.", "No duplicar libro contable; conciliación y permisos."],
        ["IA/copilot", "Ayuda a redactar, buscar y resumir contexto.", "Envío de datos opt-in/por clasificación; proveedor, retención y no entrenamiento contractual definidos."],
    ], [1800,3400,4160], 16)
    d.heading("12.2 Flujo de CI/CD propuesto", 2)
    d.code("Commit/PR -> revisión humana -> lint + análisis estático -> unit tests\n-> integración DB/archivos simulados -> escaneo dependencias/secretos\n-> build artifact inmutable -> staging + migración compatible\n-> smoke + E2E escenarios S1-S6 -> aprobación de release\n-> promover el mismo artifact -> observabilidad -> rollback si falla")
    d.p("El pipeline es objetivo, no capacidad existente confirmada. Separar migraciones de datos, usar cambios expand/contract cuando haya múltiples versiones, mantener rollback posible y evitar compilar artefactos diferentes entre staging y producción.")

    d.heading("13. Observabilidad y operación", 1)
    d.table(["Señal", "Ejemplos", "Acción"], [
        ["Logs", "request_id, actor_id, tenant_id, route, resultado, duración; sin tokens o cuerpos sensibles.", "Buscar fallas y reconstruir transacciones."],
        ["Métricas técnicas", "p50/p95/p99, 4xx/5xx, pool DB, disco, cola y latencia externa.", "Alertar en síntomas que afectan usuarios."],
        ["Trazas", "request web → DB → almacenamiento → proveedor externo.", "Aislar latencia y fallas por dependencia."],
        ["Métricas de servicio", "proyectos en riesgo, aging de incidentes, aceptación, defectos por release.", "Definir denominadores y evitar ranking punitivo individual."],
        ["Auditoría", "aprobaciones, cambios de rol, descarga sensible, deploy y exportación.", "Investigación con acceso limitado e integridad protegida."],
    ], [1900,4650,2810], 16)
    d.heading("13.1 Runbooks mínimos", 2)
    for x in ["Restaurar DB y validar consistencia.", "Revocar todas las sesiones de un usuario comprometido.", "Revertir release y verificar health checks.", "Responder a pérdida de almacenamiento/archivo huérfano.", "Gestionar incidente de exposición de información cliente.", "Rotar credencial OAuth/IA y deshabilitar integración.", "Operar cuando el proveedor de correo o IA está degradado."]: d.bullet(x)

    d.heading("14. Brechas y decisiones de arquitectura", 1)
    d.heading("14.1 Brechas principales", 2)
    d.table(["Prioridad", "Brecha", "Impacto", "Primer paso"], [
        ["P0", "No hay entidad/aislamiento de cliente; datos de negocio mezclados en Project.", "Confidencialidad y operación multiempresa.", "Taller de tenant, cliente, contactos, membresías y portal cliente."],
        ["P0", "Estados mezclan aprobado, completed, producción y soporte.", "Reportes falsos y despliegues sin evidencia.", "Definir máquinas de proyecto, aceptación, release e incidente."],
        ["P0", "Autorización dispersa entre router y controlador.", "IDOR/cross-tenant.", "Matriz de autorización y policy central probada por recurso."],
        ["P1", "Falta contrato, baseline y Change Request.", "Trabajo no facturable y alcance disputado.", "Versionado de oferta/alcance y aprobaciones."],
        ["P1", "Files bajo uploads públicos; no hay módulo release/incidente.", "Filtración de código/evidencia y operación reactiva.", "Storage privado, release y soporte MVP."],
        ["P1", "Sin pipeline/tests automatizados evidentes.", "Cambios rompen flujos y se despliegan sin señal.", "CI mínima, pruebas de políticas y escenarios."],
        ["P2", "Comunidad, chat y gamificación no definidos por política.", "Ruido, presión o exposición de datos.", "Reglas de audiencia, notificaciones, moderación y opt-in."],
        ["P2", "Indicadores promocionales sin telemetría verificada.", "Decisiones por números inventados.", "Definir owner, fuente y fórmula de cada KPI."],
    ], [800,2850,2550,3160], 14)
    d.heading("14.2 Decisiones pendientes (ADR)", 2)
    adrs = [
        ["ADR-001", "Producto: mercado, tipos de contrato y sistemas externos que seguirán siendo fuente de verdad.", "Dirección + comercial + finanzas", "Antes del diseño detallado"],
        ["ADR-002", "Tenancy: base compartida con tenant_id, schemas separados u otra opción.", "Arquitectura + seguridad + operación", "Antes de portal cliente"],
        ["ADR-003", "Autenticación: OAuth corporativo, MFA, recuperación y proveedores.", "Seguridad + negocio", "Antes de producción"],
        ["ADR-004", "Hosting: cPanel/XAMPP demo frente a VPS/cloud administrado.", "Operaciones + dirección", "Antes de SLO y costos"],
        ["ADR-005", "Archivos privados: filesystem protegido u object storage y antivirus.", "Seguridad + Ops", "Antes de datos de clientes"],
        ["ADR-006", "Estados oficiales y significado de aceptación/producción/soporte.", "PM, QA, cliente, Ops", "Antes de rediseñar dashboards"],
        ["ADR-007", "Modelo de trabajo: proyectos, tareas, sprints y esfuerzo mínimo útil.", "PM + equipo", "Antes de módulo de ejecución"],
        ["ADR-008", "Notificación y comunicación: correo, web, chat, integración externa.", "Usuarios + plataforma", "MVP producto"],
        ["ADR-009", "IA: proveedores, privacidad, costos, fallback y casos permitidos.", "Seguridad + producto", "Antes de enviar datos reales"],
        ["ADR-010", "Reporting: KPIs, definiciones y visibilidad individual/colectiva.", "Dirección + RRHH/usuarios", "Antes de panel ejecutivo"],
    ]
    d.table(["ADR", "Decisión", "Responsables", "Momento"], adrs, [1000,4400,2350,1610], 15)

    d.heading("15. Hoja de ruta recomendada", 1)
    d.p("La secuencia prioriza fundamento del negocio, protección de datos y trazabilidad. Los tiempos dependen de tamaño del equipo, alcance y decisiones pendientes; se estiman por entregable, no por calendario garantizado.")
    d.table(["Fase", "Resultado", "Alcance", "Salida verificable"], [
        ["0. Alinear", "Producto y proceso acordados.", "Entrevistas, service blueprint, contratos, definición de roles, métricas y datos.", "Mapa AS-IS/TO-BE, ADR inicial, glosario y criterios de éxito firmados."],
        ["1. Fundamento seguro", "Base fiable.", "CI, backups, manejo de secretos, storage privado, RBAC central, audit y migraciones.", "Restauración ensayada; suite permisos; release de staging."],
        ["2. Cliente + proyecto", "Hilo cliente-proyecto.", "Organization, membership, contacts, project, contract reference, scope baseline, change request.", "Cliente crea solicitud y ve solo su espacio autorizado."],
        ["3. Delivery y QA", "Trabajo y evidencia confiables.", "Requirements, milestones, tasks, test run, defects, customer acceptance y archivos versionados.", "Escenario de entrega de extremo a extremo completado."],
        ["4. Release y soporte", "Servicio operable.", "Artifact, release, deployment, environments, incident, SLA, runbooks.", "Deploy con evidencia, rollback y comunicación probados."],
        ["5. Capacidad y finanzas", "Decisiones de cartera.", "Asignación, presupuesto, tiempo opcional, costo y reportes con definiciones.", "Decisiones comerciales conciliadas con la fuente financiera."],
        ["6. Comunidad y personalización", "Uso cotidiano sostenible.", "Notificaciones configurables, comunidad moderada, preferencias y accesibilidad.", "Pruebas con usuarios y métricas de tarea sin vigilancia invasiva."],
    ], [1400,1800,3900,2260], 14)
    d.heading("15.1 Primera iniciativa que conviene ejecutar", 2)
    d.p("Antes de codificar más pantallas, escoger dos proyectos reales de tipos distintos y recorrerlos con Account, PM, Tech Lead, QA, Dev, Ops y un cliente. Documentar dónde entra el dinero, quién acepta cada fase, qué información se comparte, qué se considera terminado y cómo se presta soporte. Convertir diferencias en variantes de política, no en estados ocultos.")
    d.heading("15.2 Trabajo técnico inmediato", 2)
    for x in [
        "Definir y documentar permisos por rol y recurso; asegurar cada ruta con policy y pruebas negativas.",
        "Corregir el almacenamiento y descarga de archivos para que entregas y código no sean públicos por URL.",
        "Diseñar migrations + test framework; automatizar login, aislamiento, CSRF, estados, archivos y aceptación.",
        "Separar estados de proyecto, QA, aceptación, deployment e incidente antes de crear analítica ejecutiva.",
        "Crear ADR para hosting, tenant, identidad y retention; no asumir que XAMPP/cPanel resuelve operación productiva.",
    ]: d.bullet(x)

    d.heading("16. Criterios de aceptación de la arquitectura", 1)
    d.p("La arquitectura puede considerarse lista para guiar un MVP cuando la dirección y el equipo respondan ‘sí’ a lo siguiente con evidencia:")
    acceptance = [
        "Cada flujo objetivo tiene dueño, entradas, salidas, decisión y excepción documentados.",
        "Cliente, organización, usuario, proyecto, contrato, alcance, QA, aceptación y release tienen definiciones no ambiguas.",
        "La matriz de autorización demuestra aislamiento entre organizaciones y entre datos internos/públicos.",
        "Los seis escenarios +1 están mapeados a módulos, transacciones, vistas de código y nodos de despliegue.",
        "Se conocen los datos que salen a correo, IA, analítica, previews y repositorios; hay política de minimización.",
        "Se acordaron objetivos medibles de disponibilidad, recuperación, rendimiento, accesibilidad y soporte.",
        "Una carga, rechazo, aceptación, release y rollback se pueden reconstruir sin depender de memoria personal.",
        "La experiencia cliente y la experiencia interna muestran la audiencia y siguiente acción de forma explícita.",
        "Existe una ruta de evolución que entrega valor por etapas y conserva un camino de reversa.",
    ]
    for i, item in enumerate(acceptance, 1): d.numbered(f"{i:02d}.", item)

    d.heading("17. Glosario", 1)
    d.table(["Término", "Definición de trabajo"], [
        ["Account / organización", "Entidad cliente con identidad, contactos, acuerdos y miembros."],
        ["Baseline", "Versión aprobada de alcance, criterios, costo/fecha y supuestos contra la cual se evalúan cambios."],
        ["Change Request", "Solicitud formal de cambio con impactos y aprobación antes de ampliar compromiso."],
        ["Delivery", "Paquete de evidencia o software que se presenta a revisión."],
        ["QA approval", "Resultado técnico de verificación contra criterios; no necesariamente aceptación comercial."],
        ["Customer acceptance", "Decisión contractual de un usuario autorizado sobre una entrega concreta."],
        ["Artifact", "Build inmutable identificable por versión/hash; se promueve igual entre ambientes."],
        ["Release", "Conjunto aprobado de cambios y artefactos listo para una promoción definida."],
        ["Deployment", "Ejecución de instalación/promoción en un entorno y su resultado."],
        ["Incident", "Interrupción o degradación no planeada que requiere restaurar el servicio."],
        ["SLO / SLA", "Objetivo interno de nivel de servicio / compromiso contractual con condiciones."],
        ["Tenant", "Límite lógico de datos y usuarios de una organización cliente."],
        ["Outbox", "Registro transaccional para publicar eventos/notificaciones confiablemente después del commit."],
        ["ADR", "Registro corto de decisión arquitectónica con contexto, opción, consecuencias y estado."],
    ], [2400,6960], 16)

    d.heading("18. Referencias y anexos", 1)
    d.heading("18.1 Referencias", 2)
    refs = [
        "[1] Kruchten, Philippe. Architectural Blueprints—The “4+1” View Model of Software Architecture. IEEE Software, Vol. 12, No. 6, Nov. 1995, pp. 42–50. https://doi.org/10.1109/52.469759 ; registro IEEE: https://ieeexplore.ieee.org/document/469759/ .",
        "[2] Sweller, John. Cognitive Load During Problem Solving: Effects on Learning. Cognitive Science, 12(2), 257–285, 1988. https://doi.org/10.1207/s15516709cog1202_4 .",
        "[3] W3C. Web Content Accessibility Guidelines (WCAG) 2.2, W3C Recommendation, 12 Dec. 2024. https://www.w3.org/TR/WCAG22/ .",
        "[4] Repositorio analizado: Devioz-Portafolio-de-Proyectos; bootstrap/app.php, database/schema.sql, app/Controllers, app/Models, app/Core, views, config y README. Inspección estática para esta versión del documento.",
    ]
    for ref in refs: d.p(ref, after=150)
    d.heading("18.2 Inventario técnico inspeccionado", 2)
    d.table(["Artefacto", "Uso en el análisis"], [
        ["bootstrap/app.php", "Inventario de rutas y middleware."],
        ["database/schema.sql", "Entidades, claves, enumeraciones y constraints existentes."],
        ["app/Controllers/*.php", "Casos de uso y límites actuales de autorización."],
        ["app/Models/*.php", "Relaciones y comportamiento del dominio existente."],
        ["app/Core/{Router,Auth,Session,Uploader,Database}.php", "Infraestructura, seguridad y persistencia."],
        ["views/**, public/js/app.js", "Flujos visibles e interacciones de usuario."],
        ["composer.json, config/**, README.md", "Stack, configuración y documentación declarada."],
    ], [3600,5760], 16)
    d.heading("18.3 Preguntas para el taller de validación", 2)
    questions = [
        "¿Quién compra, quién usa, quién acepta y quién paga? ¿Puede ser la misma persona?",
        "¿Qué modalidades comerciales vende Devioz y cómo se calcula el éxito en cada una?",
        "¿Qué sistemas ya son fuente de verdad para clientes, tareas, Git, facturación, identidad y soporte?",
        "¿Qué ve el cliente durante desarrollo y qué notas deben ser siempre internas?",
        "¿Qué criterios deben cumplirse para ‘terminado’, ‘aceptado’, ‘desplegado’ y ‘cerrado’?",
        "¿Qué datos/archivos no pueden almacenarse en hosting compartido? ¿Quién define retención y borrado?",
        "¿Qué disponibilidad, soporte fuera de horario, RPO y RTO se prometen contractualmente?",
        "¿Qué señales de equipo ayudan sin generar vigilancia o incentivos perversos?",
        "¿Qué idioma, zona horaria, dispositivo y accesibilidad requieren clientes y equipos?",
        "¿Qué módulos deben quedar fuera del primer MVP porque una herramienta existente ya resuelve bien el problema?",
    ]
    for q in questions: d.bullet(q)
    d.note("Cierre", "Este documento da una dirección para conversar y priorizar. La arquitectura final nace al validar estos escenarios, registrar decisiones y revisar que el código, los procesos y la operación siguen contando la misma historia.", TEAL)
    return d


def document_xml(d: Doc) -> str:
    sect = '''<w:sectPr><w:headerReference w:type="default" r:id="rId2"/><w:footerReference w:type="default" r:id="rId3"/><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="900" w:right="1440" w:bottom="900" w:left="1440" w:header="420" w:footer="420" w:gutter="0"/><w:cols w:space="720"/><w:docGrid w:linePitch="360"/></w:sectPr>'''
    return f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><w:body>{''.join(d.body)}{sect}</w:body></w:document>'''


def make_docx(d: Doc):
    OUT.parent.mkdir(parents=True, exist_ok=True)
    content_types = ['<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>',
                     '<Default Extension="xml" ContentType="application/xml"/>',
                     '<Default Extension="png" ContentType="image/png"/>',
                     '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>',
                     '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>',
                     '<Override PartName="/word/header1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"/>',
                     '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>',
                     '<Override PartName="/word/settings.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml"/>']
    root_rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>'''
    rels = ['<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>',
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header" Target="header1.xml"/>',
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>'] + d.rels
    header = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="8" w:space="5" w:color="{TEAL}"/></w:pBdr></w:pPr>{run("DEVIOZ  |  ARQUITECTURA 4+1", True, color=NAVY, size=17)}{run("                                      Documento de trabajo · v1.0", color=MUTED, size=16)}</w:p></w:hdr>'''
    footer = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:p><w:pPr><w:jc w:val="center"/></w:pPr>{run("Devioz · Arquitectura objetivo propuesta · 15 oct 2026  |  Página ", color=MUTED, size=16)}<w:r><w:fldChar w:fldCharType="begin"/></w:r><w:r><w:instrText xml:space="preserve"> PAGE </w:instrText></w:r><w:r><w:fldChar w:fldCharType="end"/></w:r></w:p></w:ftr>'''
    settings = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><w:settings xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:updateFields w:val="true"/><w:defaultTabStop w:val="720"/></w:settings>'''
    document_rels = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">{''.join(rels)}</Relationships>'''
    with zipfile.ZipFile(OUT, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=8) as z:
        z.writestr("[Content_Types].xml", f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">{''.join(content_types)}</Types>''')
        z.writestr("_rels/.rels", root_rels)
        z.writestr("word/document.xml", document_xml(d))
        z.writestr("word/styles.xml", make_styles())
        z.writestr("word/_rels/document.xml.rels", document_rels)
        z.writestr("word/header1.xml", header)
        z.writestr("word/footer1.xml", footer)
        z.writestr("word/settings.xml", settings)
        for name, data in d.images:
            z.writestr(f"word/media/{name}", data)


def main():
    with tempfile.TemporaryDirectory(prefix="devioz-4plus1-") as tmpdir:
        diagrams = build_diagrams(Path(tmpdir))
        doc = author_doc(diagrams)
        make_docx(doc)
    print(f"Created: {OUT}")
    print(f"Headings: {doc.heading_count}; diagrams: {len(doc.images)}; size: {OUT.stat().st_size:,} bytes")


if __name__ == "__main__":
    main()
