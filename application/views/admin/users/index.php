<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>VLESS Users</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f5f5f5;
        }

        .container {
            background: white;
            padding: 25px;
            border-radius: 8px;
        }

        h1 {
            margin-top: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background: #f0f0f0;
        }

        .active {
            color: green;
            font-weight: bold;
        }

        .disabled {
            color: red;
            font-weight: bold;
        }

        .expired {
            color: orange;
            font-weight: bold;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>VLESS Users</h1>

    <p>
        <a href="<?= site_url('admin/users/create') ?>">
            + Add User
        </a>
    </p>

    <table>

        <thead>

        <tr>

            <th>ID</th>
            <th>Username</th>
            <th>Server</th>
            <th>UUID</th>
            <th>Status</th>
            <th>Traffic</th>
            <th>Expires</th>
            <th>Actions</th>

        </tr>

        </thead>

        <tbody>

        <?php if (!empty($users)): ?>

            <?php foreach ($users as $user): ?>

                <tr>

                    <td>
                        <?= (int) $user->id ?>
                    </td>

                    <td>
                        <?= html_escape($user->username) ?>
                    </td>

                    <td>
                        <?= html_escape($user->server_name) ?>
                    </td>

                    <td>
                        <code>
                            <?= html_escape($user->uuid) ?>
                        </code>
                    </td>

                    <td class="<?= html_escape($user->status) ?>">
                        <?= html_escape($user->status) ?>
                    </td>

                    <td>

                        <?= number_format(
                            $user->traffic_used_bytes / 1073741824,
                            2
                        ) ?>

                        GB

                        <?php if ($user->traffic_limit_bytes !== null): ?>

                            /
                            <?= number_format(
                                $user->traffic_limit_bytes / 1073741824,
                                2
                            ) ?>

                            GB

                        <?php endif; ?>

                    </td>

                    <td>
                        <?= html_escape($user->expires_at ?: 'Never') ?>
                    </td>

                    <td>

                        <a href="#">
                            Edit
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

        <?php else: ?>

            <tr>

                <td colspan="8">
                    No VLESS users yet.
                </td>

            </tr>

        <?php endif; ?>

        </tbody>

    </table>

</div>

</body>

</html>