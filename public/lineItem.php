<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.2/css/all.css" integrity="sha384-oS3vJWv+0UjzBfQzYUhtDYW+Pj2yciDJxpsK1OYPAYjqT085Qq/1cq5FLXAZQ7Ay" crossorigin="anonymous">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="/public/css/main.css">
        <title>AdsManager</title>
    </head>
    <body>
        <!--NAVBAR-->
        <nav class="navbar navbar-expand-ig navbar-dark bg-dark">
            <a href="/" class="navbar-brand">Ads Manager</a>

<!--            <ul class="nav">-->
<!--                <li class="nav-item">-->
<!--                    <button class="btn btn-link nav-link empty">Empty</button>-->
<!--                </li>-->
<!--            </ul>-->
        </nav>
        <!--CONTENT-->
        <section id="content">
            <!--TABLE FOR NEW LINE ITEM-->
            <div class="container">
                <table class="table table-bordered  mt-5 d-none">
                    <thead class="thad">
                        <tr>
                            <th>SERVICE</th>
                            <th>ID</th>
                            <th>NAME</th>
                        </tr>
                    </thead>
                    <tbody class="tbody"></tbody>
                </table>
            </div>
            <!--#loading-->
            <div id="loading" class="d-none">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
        </section>

        <!--SCRIPTS-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<!--        <script src="/public/js/main.js"></script>-->
        <script src="/public/js/report.js"></script>
    </body>
</html>