<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>Edit VLESS User</title>

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

        .uuid {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            word-break: break-all;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Edit VLESS User</h1>

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


    <div class="form-group">

        <label>UUID</label>

        <div class="uuid">
            <?= html_escape($user->uuid) ?>
        </div>

        <small>
            UUID cannot be changed.
        </small>

    </div>


    <form method="post"
          action="<?= site_url(
              'admin/users/edit/' . $user->id
          ) ?>">


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
                    $form['username']
                ) ?>"
            >

        </div>


        <div class="form-group">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                name="email"
                id="email"
                maxlength="255"
                value="<?= html_escape(
                    $form['email']
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

                <?php foreach ($servers as $server): ?>

                    <option
                        value="<?= (int) $server->id ?>"
                        <?= (
                            $form['server_id']
                            == $server->id
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= html_escape(
                            $server->name
                        ) ?>

                        -
                        <?= html_escape(
                            $server->domain
                        ) ?>

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
                value="<?= html_escape(
                    $form['traffic_limit_gb']
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
                value="<?= html_escape(
                    $form['duration_days']
                ) ?>"
            >

            <small>
                Leave empty for no expiration.
            </small>

        </div>


        <div class="form-group">

            <label for="status">
                Status
            </label>

            <select
                name="status"
                id="status"
            >

                <option
                    value="active"
                    <?= $form['status'] === 'active'
                        ? 'selected'
                        : '' ?>
                >
                    Active
                </option>

                <option
                    value="disabled"
                    <?= $form['status'] === 'disabled'
                        ? 'selected'
                        : '' ?>
                >
                    Disabled
                </option>

                <option
                    value="expired"
                    <?= $form['status'] === 'expired'
                        ? 'selected'
                        : '' ?>
                >
                    Expired
                </option>

            </select>

        </div>


        <button type="submit">
            Save Changes
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