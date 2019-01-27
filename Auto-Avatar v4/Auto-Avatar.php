<?  # Авто-Аватар by Somerholder
    # Мой Вконтакте: vk.com/id92774

    # Пример подключения:
    # photoUpdater('Логин', 'Пароль', 'Тайм-Зона');

    # Дата создания 24.12.2016

    $core = new photoUpdater('+799999999999', 'topPassword1337', 'Europe/Moscow');
    $core->checkExistsPhoto();  # Проверка на наличие фотографии
    $core->auth();              # Авторизация в Вконтакте
    $core->createPhoto();       # Создание фотографии
    $core->upload();            # Загрузка фотографии на сервер

    # Не советую тебе ничего не трогать впереди !!!
    class photoUpdater {
      function __construct($login, $password, $timezone){
        echo "\r\n-> Auto-Avatar v2 by Somerholder (vk.com/id92774) <-\r\n\r\n";
        date_default_timezone_set($timezone);
        $this->file = 'photo.png';
        $this->login = $login;
        $this->password = $password;
      }

      # Проверяем на наличие фотографии
      public function checkExistsPhoto(){
        if(!file_exists($this->file))
          exit("-> File with photo not found! \r\n");
      }

      # Авторизация в Вконтакте
      public function auth(){
        # Если нету файла с Куками то создаём.
          if(!file_exists('cookie')) {
            $get->request = $this->curl('http://vk.com/');
            preg_match('/name="lg_h" value="(.*?)"/', $get->request, $lg_h);
            preg_match('/name="ip_h" value="(.*?)"/', $get->request, $ip_h);
            $this->curl('https://login.vk.com/?act=login', array('act' => 'login', 'role' => 'al_frame', '_origin' => 'https://vk.com', 'ip_h' => $ip_h[1], 'lg_h' => $lg_h[1], 'email' => $this->login, 'pass' => $this->password), true);
            echo "-> Cookie file created \r\n";
          } else echo "-> File Cookie exists, continue \r\n";

          # Проходим авторизацию
          $get->info = $this->curl('https://vk.com/feed2.php', false, true);
          if($get->info->user->id != '-1') echo "-> Logined as id".$get->info->user->id." by Cookie \r\n";
            else { echo "-> Error auth \r\n"; unlink('cookie'); exit; }
      }

      # Создаём резулат аватарки
      public function createPhoto(){
        # Получаем инфу через Токен
        $vkauth = $this->curl('https://oauth.vk.com/token?grant_type=password&client_id=2274003&client_secret=hHbZxrka2uZ6jB1inYsH&username='.$this->login.'&password='.$this->password, false, true);
        $execute = $this->curl('https://api.vk.com/method/execute?code=return[API.messages.get(),API.users.get({"fields":"counters"}),API.photos.get({"album_id":"profile","rev":1,"extended":1,"count":1})];&v=5.59&access_token='.$vkauth->access_token, false, true);
        if($execute->error) exit($execute->error->error_msg."\n");
          elseif($execute->response) echo "-> Creating Image \n";
          $this->photo = $execute->response[2]->items[0]->owner_id.'_'.$execute->response[2]->items[0]->id;

          $getinfo->time = date('H');
          if($getinfo->time >= '00' && $getinfo->time < '4') $time = 'Night';
            elseif($getinfo->time >= '4' && $getinfo->time < '12') $time = 'Morning';
            elseif($getinfo->time >= '12' && $getinfo->time < '18') $time = 'Day';
            elseif($getinfo->time >= '18' && $getinfo->time < '24') $time = 'Evening';

        # Создаём изображение
        $top = imagecreatefrompng($this->file);
        $size = getimagesize($this->file);
        $image = imagecreatefrompng($this->file);
        $color = imagecolorallocate($image, 255, 255, 255);
        imagecopyresampled($image, $top, 0, 0, 0, 0, $size[0], $size[1], $size[0], $size[1]);

        imagettftext($image, 45, 0, 300, 643, $color, 'font.otf', $this->format($execute->response[0]->count)); # Сообщений
        imagettftext($image, 45, 0, 220, 705, $color, 'font.otf', $this->format($execute->response[1][0]->counters->friends)); # Друзей
        imagettftext($image, 45, 0, 355, 825, $color, 'font.otf', $this->format($execute->response[1][0]->counters->followers)); # Подписчиков
        imagettftext($image, 45, 0, 218, 765, $color, 'font.otf', $this->format($execute->response[2]->items[0]->likes->count)); # Лайков
        imagettftext($image, 45, 0, 250, 585, $color, 'font.otf', $time); # Время Суток
        imagettftext($image, 45, 0, 290, 885, $color, 'font.otf', date('d.m | H:i')); # Время Обновления
        imagepng($image, 'result.png') or exit("-> Error Saving Result... \n"); imagedestroy($image);
      }

      # Загружаем фотографию на сервер
      public function upload(){
        $get->link = $this->curl('https://vk.com/al_photos.php?act=edit_photo&al=1&photo='.$this->photo);
        $get->alb = preg_match('#ToAlbum.val\((.*?)\)#', $get->link, $get->album);
        $get->info = preg_match("#_url\":\"(.*?)\",(.*?),(.*?), (.*?), (.*?), '".$this->photo."', '(.*?)'#", $get->link, $get->inf);
        $get->uploadurl = preg_replace('#http\:\\\/\\\/#', '', $get->inf['1']); $get->uploadurl = preg_replace('#\\\/#', '/', $get->uploadurl);
        $get->upload = $this->curl($get->uploadurl, array('Filename' => 'Filtered.jpg', 'photo' => $this->uploadtype('result.png'), 'Upload' => 'Submit Query'));
        exit("-> Uploading 'result.png' on server... \r\n-> Result: ".$this->curl('https://vk.com/al_photos.php?_query='.$get->upload.'&act=save_desc&aid='.$get->alb[1].'&al=1&hash='.$get->inf[6].'&photo='.$this->photo)."\r\n");
      }

      # Используем CURL для запросов к VK
      public function curl($url, $post = false, $decode = false){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
        if($post){ curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $post); }
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie');
        $response = curl_exec($ch); curl_close($ch);
        if($decode == true){
          return json_decode($response);
        } else {
          return $response;
        }
      }

      public function format($num){
        # P.S Функция не моя
        # Иван Буров aka VkServ дал её
        # Я Слегка её исправил и убрал говнокод
        $m = floor($num / 1000000);
        $k = floor($num / 1000);
        if($m != 0){
            $m = $num / 1000000;
            $num = round($m, 1);
            $num = $num.'M';
        } elseif($k != 0){
            $k = $num / 1000;
            $num = round($k, 1);
            $num = $num.'K';
        } else $num = (int) $num;

        return $num;
      }

      public function uploadtype($file){
        # Функция определяет версию PHP
        # И выбирает как закачивать указанный файл.
        # By Somerholder
        $version = phpversion();
		    return $version >= '5.6' ? new CURLFile($file) : '@'.$file;
      }
  }
