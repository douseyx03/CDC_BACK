<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>API Platform Viewer</title>
    <script src="https://unpkg.com/@stoplight/elements/web-components.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements/styles.min.css">
    <style>body { margin: 0; font-family: sans-serif; }</style>
</head>
<body>
<elements-openapi
    apiDescriptionUrl="{{ url('/openapi.yaml') }}"
    router="hash"
    layout="sidebar"
></elements-openapi>
</body>
</html>
