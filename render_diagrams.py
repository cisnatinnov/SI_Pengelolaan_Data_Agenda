#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Render all Mermaid diagrams from docs/UML_Documentation.md to PNG images
using the mermaid.ink online service (https://mermaid.ink).

Usage:
    python render_diagrams.py

Output:
    docs/diagrams/<slug>.png  (one file per Mermaid block)
"""

import base64
import json
import re
import sys
import urllib.request
from pathlib import Path

BASE = Path(__file__).resolve().parent
MD_PATH = BASE / "laravel-vue-mvc/docs/UML_Documentation.md"
OUT_DIR = BASE / "laravel-vue-mvc/docs/diagrams"
THEME = "default"

BLOCK_RE = re.compile(r"```(mermaid)\s*\n(.*?)```", re.S)


def slugify(heading, index):
    base = re.sub(r"[^a-z0-9]+", "_", heading.lower()).strip("_")
    if not base:
        base = f"diagram_{index}"
    return f"{index:02d}_{base}"


def safe_ascii(value, limit=50):
    """Sanitize console output so non-ASCII chars (e.g. arrows) never break printing."""
    return value[:limit].encode("ascii", "replace").decode("ascii")


def fetch_png(code: str, out_file: Path) -> bool:
    payload = json.dumps({"code": code, "mermaid": {"theme": THEME}}).encode("utf-8")
    encoded = base64.urlsafe_b64encode(payload).decode("ascii")
    url = f"https://mermaid.ink/img/{encoded}?type=png"
    req = urllib.request.Request(url, headers={"User-Agent": "opencode-uml-docs"})
    with urllib.request.urlopen(req, timeout=60) as resp:
        data = resp.read()
    if not data:
        return False
    out_file.write_bytes(data)
    return True


def main():
    md = MD_PATH.read_text(encoding="utf-8")
    lines = md.split("\n")
    OUT_DIR.mkdir(parents=True, exist_ok=True)

    blocks = list(BLOCK_RE.finditer(md))
    if not blocks:
        print("No Mermaid blocks found.")
        return 1

    index = 0
    for m in blocks:
        index += 1
        code = m.group(2).strip()

        line_start = md[: m.start()].count("\n")
        heading = ""
        for ln in range(line_start, max(-1, line_start - 12), -1):
            s = lines[ln].strip()
            if s.startswith("#"):
                heading = s.lstrip("#").strip()
                break

        name = slugify(heading, index)
        out = OUT_DIR / f"{name}.png"
        try:
            ok = fetch_png(code, out)
            print(f"[{'OK ' if ok else 'FAIL'}] {name}  ({safe_ascii(heading)})")
            if not ok:
                sys.exit(1)
        except Exception as exc:  # noqa: BLE001
            print(f"[FAIL] {name}  ({safe_ascii(heading)})  -> {safe_ascii(str(exc), 120)}")
            return 1

    print(f"\nRendered {index} diagrams into {OUT_DIR}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())