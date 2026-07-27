#!/usr/bin/env python3
"""Publie (ou met à jour) un article depuis son JSON vers WordPress via REST.

Idempotent sur le slug : recrée le contenu si l'article existe déjà, sinon le
crée. Renseigne les champs structurés (L'essentiel, sources, date vérifiée,
title/meta SEO) exposés par le thème 0.5.1.

Utilise curl (le proxy du bac à sable casse le SSL de Python).

Usage :
    python3 scripts/publier_article.py content/articles/mon-article.json
    python3 scripts/publier_article.py content/articles/mon-article.json --draft
"""
import argparse
import json
import os
import subprocess
import sys

BASE = "https://www.infoweb-manutention.fr/wp-json/wp/v2/"


def creds():
    p = os.path.expanduser("~/.claude.json")
    env = json.load(open(p))["mcpServers"]["infoweb-wp"]["env"]
    return env["WP_API_USERNAME"], env["WP_API_PASSWORD"]


def curl(method, path, user, pwd, payload=None):
    cmd = ["curl", "-s", "-u", f"{user}:{pwd}", "-X", method, BASE + path]
    if payload is not None:
        cmd += ["-H", "Content-Type: application/json", "--data-binary", json.dumps(payload)]
    out = subprocess.run(cmd, capture_output=True, text=True, timeout=90).stdout
    try:
        return json.loads(out)
    except json.JSONDecodeError:
        print("Réponse non-JSON :", out[:500])
        sys.exit(1)


def cat_id(slug, user, pwd):
    if not slug:
        return None
    r = curl("GET", f"categories?slug={slug}&_fields=id", user, pwd)
    return r[0]["id"] if r else None


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("fichier")
    ap.add_argument("--draft", action="store_true", help="Publie en brouillon.")
    args = ap.parse_args()

    art = json.load(open(args.fichier, encoding="utf-8"))
    user, pwd = creds()

    cid = cat_id(art.get("categorie", ""), user, pwd)
    if art.get("categorie") and not cid:
        print(f"⚠ catégorie '{art['categorie']}' introuvable — publie sans catégorie.")

    meta = {}
    if art.get("verifie_le"):
        meta["_infoweb_verifie_le"] = art["verifie_le"]
    if art.get("essentiel"):
        meta["_infoweb_essentiel"] = "\n".join(art["essentiel"])
    if art.get("sources_meta"):
        meta["_infoweb_sources"] = "\n".join(art["sources_meta"])
    if art.get("titre_seo"):
        meta["_infoweb_titre_seo"] = art["titre_seo"]
    if art.get("description_seo"):
        meta["_infoweb_description_seo"] = art["description_seo"]

    payload = {
        "title": art["titre"],
        "slug": art["slug"],
        "status": "draft" if args.draft else "publish",
        "excerpt": art.get("chapo", ""),
        "content": art["contenu"],
    }
    if cid:
        payload["categories"] = [cid]
    if meta:
        payload["meta"] = meta

    existing = curl("GET", f"posts?slug={art['slug']}&status=any&_fields=id", user, pwd)
    if existing:
        pid = existing[0]["id"]
        res = curl("POST", f"posts/{pid}", user, pwd, payload)
        action = "mis à jour"
    else:
        res = curl("POST", "posts", user, pwd, payload)
        action = "créé"

    if res.get("id"):
        m = res.get("meta", {})
        print(f"✓ {action} : id {res['id']} — {res.get('status')}")
        print(f"  {res.get('link')}")
        print(f"  catégorie: {cid or '—'} | vérifié: {m.get('_infoweb_verifie_le','—')} "
              f"| essentiel: {'oui' if m.get('_infoweb_essentiel') else 'non'} "
              f"| sources: {'oui' if m.get('_infoweb_sources') else 'non'}")
    else:
        print("✗ échec :", json.dumps(res)[:400])
        sys.exit(1)


if __name__ == "__main__":
    main()
