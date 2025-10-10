<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>API Platform Viewer</title>
    <link rel="stylesheet" href="https://unpkg.com/@stoplight/elements/styles.min.css">
    <style>
        html, body { margin: 0; height: 100%; font-family: sans-serif; }
        elements-api { height: 100%; display: block; }
    </style>
</head>
<body>
<elements-api router="hash" layout="sidebar"></elements-api>

<script type="module">
    import 'https://unpkg.com/@stoplight/elements/web-components.min.js';

    const element = document.querySelector('elements-api');
    element.apiDescriptionUrl = '{{ url('/openapi.yaml') }}';
</script>
</body>
</html>
