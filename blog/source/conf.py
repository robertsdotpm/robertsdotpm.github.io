from datetime import datetime

# Configuration file for the Sphinx documentation builder.

# -- Project information

project = "Aul Ma's research facility"
author = 'Matthew Roberts'
copyright = f'{datetime.now().year}, {author}'

release = '0.1'
version = '3.0.0'

# -- General configuration

extensions = [
    'sphinx_rtd_theme',
    'sphinx.ext.duration',
    'sphinx.ext.doctest',
    'sphinx.ext.autodoc',
    'sphinx.ext.autosummary',
    'sphinx.ext.intersphinx',
]

intersphinx_mapping = {
    'python': ('https://docs.python.org/3/', None),
    'sphinx': ('https://www.sphinx-doc.org/en/master/', None),
}
intersphinx_disabled_domains = ['std']

templates_path = ['_templates']

# -- Options for HTML output

html_theme = 'sphinx_rtd_theme'

html_theme_options = {
    "style_external_links": False,
}

html_show_sphinx = False

# -- Options for EPUB output
epub_show_urls = 'footnote'


html_context = {
    "footer_mirrors": """
    <div style="text-align: left; margin-top: 10px;">

            <strong>Mirrors:</strong> 
            <a href="https://robertsdotpm.github.io/">Github Pages</a> | 
            <a href="https://robertsdotpm.readthedocs.io/">Read The Docs</a>

    </div>
    """
}

