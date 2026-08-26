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
    <?php if (!empty($success)): ?>

        <div style="
        background:#d1e7dd;
        color:#0f5132;
        padding:12px;
        margin-bottom:20px;
        border-radius:5px;
    ">
            <?= html_escape($success) ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div style="
        background:#f8d7da;
        color:#842029;
        padding:12px;
        margin-bottom:20px;
        border-radius:5px;
    ">
            <?= html_escape($error) ?>
        </div>

    <?php endif; ?>
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

                        <a href="<?= site_url(
                            'admin/users/edit/' . $user->id
                        ) ?>">
                            Edit
                        </a>

                        |

                        <?php if ($user->status === 'active'): ?>

                            <a
                                    href="<?= site_url(
                                        'admin/users/toggle/' . $user->id
                                    ) ?>"
                                    onclick="return confirm(
                'Disable this user?'
            );"
                            >
                                Disable
                            </a>

                        <?php elseif ($user->status === 'disabled'): ?>

                            <a
                                    href="<?= site_url(
                                        'admin/users/toggle/' . $user->id
                                    ) ?>"
                                    onclick="return confirm(
                'Enable this user?'
            );"
                            >
                                Enable
                            </a>

                        <?php else: ?>

                            <span>
            Expired
        </span>

                        <?php endif; ?>
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