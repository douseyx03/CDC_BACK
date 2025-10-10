<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css">
    <style>body { margin: 0; } #swagger-ui { height: 100vh; }</style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
<script>
window.onload = () => {
  SwaggerUIBundle({
    url: '{{ url('/openapi.yaml') }}',
    dom_id: '#swagger-ui'
  });
};
</script>
</body>
</html>
