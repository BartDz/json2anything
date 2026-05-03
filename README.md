# json2anything

Paste JSON → get YAML, PHP array, TypeScript interface, SQL INSERT, or CSV instantly. Live preview, no page reload.

## Features

- **5 output formats**: YAML · PHP array · TypeScript interface · SQL INSERT · CSV
- **Live preview** — debounced 300ms, updates as you type
- **JSON validation** with line + column error info
- **Copy button** for instant clipboard access
- **CodeMirror 6** editor with syntax highlighting
- **Dark theme** by default

## Stack

- PHP 8.3 + Slim 4 (server-side conversion)
- ext-yaml (YAML output)
- Vanilla JS + CodeMirror 6 (ESM CDN)
- Bulma 1.0.4 (UI)
- Docker (php:8.3-apache)

## API

```
POST /convert
Body:     { "input": "{...}", "from": "json", "to": "typescript" }
Response: { "output": "interface Root { ... }", "error": null }
```

## Running locally

```bash
docker compose up --build
# open http://localhost:8082
```

## Tests

```bash
docker compose exec app ./vendor/bin/phpunit
```

## Project structure

```
public/
  index.php       # Slim 4 entry point + routing
  index.html      # UI shell
  assets/
    app.js        # Live preview, tabs, copy button
    style.css     # Split-pane layout, dark theme
src/Converter/
  YamlConverter.php
  PhpArrayConverter.php
  TypeScriptConverter.php
  SqlConverter.php
  CsvConverter.php
tests/Converter/  # PHPUnit tests per converter
```
