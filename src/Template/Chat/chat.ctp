<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>ChatGPT Integration</title>
</head>
<body>

<div class="container mt-5">
    <h1>ChatGPT Integration</h1>

    <?= $this->Form->create() ?>
    <div class="form-group">
        <label for="userInput">User Input:</label>
        <input type="text" name="user_input" id="userInput" class="form-control" required>
    </div>

    <button type="submit" class="btn btn-primary">Generate Response</button>

    <?= $this->Form->end() ?>

    <?php if (isset($generatedText)): ?>
        <div class="mt-3">
            <strong>Generated Response:</strong>
            <?= h($generatedText) ?>
        </div>
    <?php endif; ?>

    <br />

    <?php if (isset($reportInfo)): ?>
        <div class="mt-3">
            <strong>Report Information:</strong>
            <?= $reportInfo ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>