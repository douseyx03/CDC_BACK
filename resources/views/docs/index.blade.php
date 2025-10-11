<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Documentation API</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; background: #f8fafc; }
        header { background: #0f172a; color: #f8fafc; padding: 2rem; text-align: center; }
        footer { background: #0f172a; color: #f8fafc; padding: 2rem; text-align: center; }
        main { max-width: 720px; margin: 2rem auto; padding: 0 1.5rem; }
        ul { list-style: none; padding: 0; }
        li { margin-bottom: 1rem; }
        a.viewer {
            display: block; padding: 1.25rem 1.5rem; border-radius: .75rem; text-decoration: none; color: #0f172a;
            background: #fff; box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08); transition: transform .2s ease, box-shadow .2s ease;
        }
        a.viewer:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12); }
        small { display: block; margin-top: .35rem; color: #475569; }
        a{color: #fbfbfbff; text-decoration: none;}
    </style>
</head>
<body>
<header>
    <h1>API Caisse de Dépots et de Consignations</h1>
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
    <section style="margin-top:2.5rem; background:#fff; border-radius:.75rem; padding:1.5rem; box-shadow:0 10px 28px rgba(15,23,42,0.08);">
        <h2 style="margin-top:0; font-size:1.25rem;">Notes de version</h2>
        <ul style="list-style:disc; padding-left:1.15rem; color:#334155;">
            <li>Les routes de gestion des agents précisent désormais que les rôles doivent exister avec le guard <code>sanctum</code>. Un envoi de chaîne séparée est automatiquement normalisé, et le seeder <code>RolePermissionSeeder</code> alimente les rôles/permissions avec ce guard.</li>
            <li>Les erreurs 422 des viewers exposent la structure de réponse commune (`message` + `errors`).</li>
            <li>Les notifications e-mail (création d’agent, mises à jour de demande) transitent par la queue <code>database</code>. Pensez à lancer <code>php artisan queue:work</code> en local.</li>
            <li>La documentation couvre désormais les modules Services/Documents (front & backoffice) ainsi que les profils utilisateurs (particulier, entreprise, institution) dans les schémas OpenAPI.</li>
        </ul>
    </section>
</main>
<footer>
    Copyright &copy; {{ date('Y') }} Caisse de Dépots et de Consignations. Tous droits réservés. Développé par Seydou Diallo(<a href="https://github.com/douseyx03" target="_blank">douseyx03</a>).
</footer>
</body>
</html>
