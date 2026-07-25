#!/usr/bin/env python3
"""Récupère le dernier snapshot Wayback des pages à recréer (phase 0).
Sortie : content/wayback/{slug}.html (brut) + content/wayback/{slug}.txt (texte extrait).
"""
import csv, html.parser, json, os, re, subprocess, sys, time

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DATA = os.path.join(BASE, 'data', 'plan-urls-a-traiter.csv')
OUT = os.path.join(BASE, 'content', 'wayback')
os.makedirs(OUT, exist_ok=True)

UA = {'User-Agent': 'Mozilla/5.0 (compatible; site-rebuild-archive-fetch)'}
N_PAGES = 45


class TextExtractor(html.parser.HTMLParser):
    SKIP = {'script', 'style', 'nav', 'footer', 'header', 'form', 'noscript', 'aside'}

    def __init__(self):
        super().__init__()
        self.parts = []
        self.skip_depth = 0
        self.current_tag = None

    def handle_starttag(self, tag, attrs):
        if tag in self.SKIP:
            self.skip_depth += 1
        self.current_tag = tag
        if tag in ('h1', 'h2', 'h3', 'h4', 'p', 'li', 'tr') and not self.skip_depth:
            self.parts.append(f"\n[{tag}] ")

    def handle_endtag(self, tag):
        if tag in self.SKIP and self.skip_depth:
            self.skip_depth -= 1

    def handle_data(self, data):
        if not self.skip_depth and data.strip():
            self.parts.append(data.strip() + ' ')

    def text(self):
        t = ''.join(self.parts)
        return re.sub(r'\n{3,}', '\n\n', t).strip()


def slugify(path):
    s = path.strip('/').replace('/', '__') or 'home'
    return re.sub(r'[^a-z0-9_.-]', '-', s.lower())[:120]


def fetch(url, timeout=25):
    r = subprocess.run(
        ['curl', '-sL', '-m', str(timeout), '-A', UA['User-Agent'], url],
        capture_output=True)
    if r.returncode != 0:
        raise RuntimeError(f'curl rc={r.returncode}')
    return r.stdout.decode('utf-8', errors='replace')


def best_snapshot(path):
    """CDX: dernier snapshot 200 de l'URL (variantes www/apex et avec/sans slash final)."""
    variants = [path]
    if path != '/' and path.endswith('/'):
        variants.append(path.rstrip('/'))
    for p in variants:
        for host in ('www.infoweb-manutention.fr', 'infoweb-manutention.fr'):
            q = (f"http://web.archive.org/cdx/search/cdx?url={host}{p}"
                 f"&output=json&filter=statuscode:200&limit=-3")
            try:
                rows = json.loads(fetch(q))
                if len(rows) > 1:
                    return rows[-1][1]  # timestamp du plus récent
            except Exception:
                pass
    return None


def main():
    with open(DATA) as f:
        rows = [r for r in csv.DictReader(f) if r['action'].startswith('RECREER')]
    rows.sort(key=lambda r: (-int(r['trafic_actuel'] or 0), -int(r['volume_cumule'] or 0)))
    targets = ['/'] + [r['url'] for r in rows if r['url'] != '/'][:N_PAGES - 1]

    results = []
    for i, path in enumerate(targets, 1):
        slug = slugify(path)
        raw_fp = os.path.join(OUT, slug + '.html')
        if os.path.exists(raw_fp):
            results.append((path, 'cached'))
            continue
        ts = best_snapshot(path)
        if not ts:
            results.append((path, 'NO_SNAPSHOT'))
            print(f"[{i}/{len(targets)}] NO_SNAPSHOT {path}", flush=True)
            continue
        url = f"https://web.archive.org/web/{ts}id_/https://www.infoweb-manutention.fr{path}"
        try:
            html_src = fetch(url)
        except Exception as e:
            results.append((path, f'ERR {e}'))
            print(f"[{i}/{len(targets)}] ERR {path} {e}", flush=True)
            time.sleep(2)
            continue
        with open(raw_fp, 'w') as f:
            f.write(f"<!-- source: {url} -->\n" + html_src)
        p = TextExtractor()
        try:
            p.feed(html_src)
        except Exception:
            pass
        with open(os.path.join(OUT, slug + '.txt'), 'w') as f:
            f.write(f"URL: {path}\nSNAPSHOT: {ts}\n\n" + p.text())
        results.append((path, ts))
        print(f"[{i}/{len(targets)}] OK {ts} {path}", flush=True)
        time.sleep(1.5)

    ok = sum(1 for _, s in results if s not in ('NO_SNAPSHOT',) and not str(s).startswith('ERR'))
    print(f"\nDONE: {ok}/{len(targets)} récupérées → {OUT}", flush=True)


if __name__ == '__main__':
    main()
