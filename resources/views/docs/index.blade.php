<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Documentation API</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; background: #f8fafc; }
        header { background: #0f172a; color: #f8fafc; padding: 2rem; text-align: center; }
        main { max-width: 720px; margin: 2rem auto; padding: 0 1.5rem; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 1rem; }
        a.viewer {
            display: block; padding: 1.25rem 1.5rem; border-radius: .75rem; text-decoration: none; color: #0f172a;
            background: #fff; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); transition: transform .2s ease, box-shadow .2s ease;
        }
        a.viewer:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12); }
        small { display: block; margin-top: .35rem; color: #475569; }
    </style>
</head>
<body>
<header>
    <h1>Documentation API CDC Backoffice</h1>
    <p>Sélectionnez votre viewer préféré.</p>
</header>
<main>
    <ul>
        <li>
            <a class="viewer" href="{{ url('/docs/swagger') }}">
                Swagger UI
                <small>Interface interactive classique (OpenAPI)</small>
            </a>
        </li>
        <li>
            <a class="viewer" href="{{ url('/docs/redoc') }}">
                ReDoc
                <small>Documentation structurée générée par ReDoc</small>
            </a>
        </li>
        <li>
            <a class="viewer" href="{{ url('/docs/api-platform') }}">
                API Platform Viewer
                <small>Viewer web-components basé sur Stoplight Elements</small>
            </a>
        </li>
    </ul>
</main>
</body>
</html>
