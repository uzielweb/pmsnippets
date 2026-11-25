import io
import os
import zipfile

ROOT = os.path.dirname(os.path.abspath(__file__))
DIST = os.path.join(ROOT, "dist")
os.makedirs(DIST, exist_ok=True)


def zip_folder(folder_path: str) -> bytes:
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, "w", zipfile.ZIP_DEFLATED) as zf:
        for base, _, files in os.walk(folder_path):
            for fname in sorted(files):
                full = os.path.join(base, fname)
                rel = os.path.relpath(full, folder_path).replace("\\", "/")
                zf.write(full, rel)
    return buf.getvalue()


def main() -> None:
    out_zip = os.path.join(DIST, "pkg_pmsnippets_v1.0.0.zip")
    with zipfile.ZipFile(out_zip, "w", zipfile.ZIP_DEFLATED) as pkg:
        pkg.write(os.path.join(ROOT, "pkg_pmsnippets.xml"), "pkg_pmsnippets.xml")
        for name, rel_dir in [('com_pmsnippets', 'com_pmsnippets'), ('mod_pmsnippets', 'mod_pmsnippets'), ('plg_finder_pmsnippets', 'plg_finder_pmsnippets'), ('plg_content_pmsnippets', 'plg_content_pmsnippets')]:
            pkg.writestr(f"packages/{name}.zip", zip_folder(os.path.join(ROOT, rel_dir)))
    print(f"Built {out_zip} ({os.path.getsize(out_zip):,} bytes)")


if __name__ == "__main__":
    main()
