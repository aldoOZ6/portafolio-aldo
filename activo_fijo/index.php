<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Activos Fijos</title>
    <style>
        body {
            background-image: url('img/moderna1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: Arial, sans-serif;
            color: black; /* Cambiado a negro */
        }
        h1 {
            text-align: center;
            margin-top: 50px;
            font-size: 40px;
            font-weight: bold;
            color: black; /* Cambiado a negro */
        }
        .opciones {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 40px;
        }

        .opciones a {
            display: block;
            background-color: yellow;
            color: black; /* Cambiado a negro */
            text-decoration: none;
            padding: 15px 30px;
            margin: 10px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 3px 3px 8px rgba(0,0,0,0.4);
            transition: background-color 0.3s;
        }

        .opciones a:hover {
            background-color: gold;
        }
    </style>
</head>
<body>
    <h1>ACTIVOS FIJOS</h1>
    <div class="opciones">
        <a href="tolvas.php">TOLVAS</a>
        <a href="unidades.php">CAJAS SECAS</a>
        <a href="tractos.php">TRACTOS</a>
        <a href="torton.php">TORTON</a>
        <a href="dolly.php">DOLLY</a>
    </div>
</body>
</html>

