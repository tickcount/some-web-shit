<html>
  <head>
    <title>Когда зарегистрировался?</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" href="/assets/main.css" type="text/css" media="all" />
    <script src="/assets/jquery.min.js"></script>
    <script src="/assets/jquery-migrate.min.js"></script>
    <script src="/assets/main.js"></script>
  </head>
  <body>
    <div class="page_block">
      <h2>
        <div class="page_block_header">
          Когда зарегистрировался в ВКонтакте?
        </div>
      </h2>
      <div class="page_block_body">
        <div id="searchUserResult"></div>
        <form id="searchForm" class="search_form"> 
          <div id="searchBlock" class="search_input_block">
            <div class="search_reset" onclick="searchReset()"></div>
            <input type="text" class="search_field" oninput="searchUser(this.value)" placeholder="Введите ссылку на страницу / ID Странички / Домен пользователя" autocomplete="off" autocorrect="off" autocapitalize="off">
          </div>
        </form>
        <div class="page_block_description">Данное приложение позволяет вам узнать точную дату и время регистрации любого пользователя ВКонтакте, что невозможно сделать, пользуясь стандартными средствами соцсети.</div>
      </div>
    </div>
  </body>
</html>