<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Create VLESS User</title>

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
            max-width: 700px;
        }

        h1 {
            margin-top: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 9px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            cursor: pointer;
        }

        .back {
            margin-left: 10px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Create VLESS User</h1>
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
    <form method="post"
          action="<?= site_url('admin/users/create') ?>">

        <div class="form-group">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                name="username"
                id="username"
                required
                maxlength="100"
                value="<?= html_escape(
                    isset($form['username'])
                        ? $form['username']
                        : ''
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="server_id">
                V2Ray Server
            </label>

            <select
                name="server_id"
                id="server_id"
                required
            >

                <option value="">
                    -- Select Server --
                </option>

                <?php foreach ($servers as $server): ?>

                    <option
                        value="<?= (int) $server->id ?>"
                        <?= (
                            isset($form['server_id'])
                            && $form['server_id'] == $server->id
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= html_escape($server->name) ?>

                        -
                        <?= html_escape($server->domain) ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <div class="form-group">

            <label for="traffic_limit_gb">
                Traffic Limit (GB)
            </label>

            <input
                type="number"
                name="traffic_limit_gb"
                id="traffic_limit_gb"
                min="0"
                step="0.01"
                placeholder="Example: 10"
                value="<?= html_escape(
                    isset($form['traffic_limit_gb'])
                        ? $form['traffic_limit_gb']
                        : ''
                ) ?>"
            >

            <small>
                Leave empty for unlimited traffic.
            </small>

        </div>


        <div class="form-group">

            <label for="duration_days">
                Duration (Days)
            </label>

            <input
                type="number"
                name="duration_days"
                id="duration_days"
                min="1"
                step="1"
                placeholder="Example: 30"
                value="<?= html_escape(
                    isset($form['duration_days'])
                        ? $form['duration_days']
                        : ''
                ) ?>"
            >

            <small>
                Leave empty for no expiration.
            </small>

        </div>


        <button type="submit">
            Create User
        </button>

        <a
            class="back"
            href="<?= site_url('admin/users') ?>"
        >
            Cancel
        </a>

    </form>

</div>

</body>

</html>