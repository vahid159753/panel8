<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>V2Ray Servers</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f5f5f5;
        }
    </style>
</head>

<body>

<h1>V2Ray Servers</h1>

<table>

    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Host</th>
        <th>Port</th>
        <th>Domain</th>
        <th>Container IP</th>
        <th>Status</th>
    </tr>
    </thead>

    <tbody>

    <?php foreach ($servers as $server): ?>

        <tr>

            <td>
                <?= (int) $server->id ?>
            </td>

            <td>
                <?= html_escape($server->name) ?>
            </td>

            <td>
                <?= html_escape($server->host) ?>
            </td>

            <td>
                <?= (int) $server->port ?>
            </td>

            <td>
                <?= html_escape($server->domain) ?>
            </td>

            <td>
                <?= html_escape($server->container_ip) ?>
            </td>

            <td>
                <?= html_escape($server->status) ?>
            </td>

        </tr>

    <?php endforeach; ?>

    </tbody>

</table>

</body>
</html>