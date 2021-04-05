<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>php render</title>
</head>
<body>
    <h3>php방식으로 랜더링</h3>
    <?php 

        echo '111111111';
        echo '<pre>';
        print_r($_GET);
        echo '</pre>';
        phpinfo();
    ?>
    
</body>
</html>