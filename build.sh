rm -rf "docs/*"
python3 -m sphinx.cmd.build "blog/source" "docs"
touch "docs/.nojekyll"