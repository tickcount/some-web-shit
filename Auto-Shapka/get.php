<?  # Авто-Шапка by Somerholder
    # Мой Вконтакте: vk.com/id92774

    # Пример подключения:
		# photoUpdater('ACCESS_TOKEN', 'ID Группы ВК', 'Тайм-Зона');
		
		# Дата создания 04.01.2017

		$core = new photoUpdater('access_token', '126796920', 'Europe/Moscow');
    $core->checkExistsPhoto();  # Проверка на наличие фотографии
    $core->auth();              # Авторизация в Вконтакте
    $core->createPhoto();       # Создание фотографии
    $core->upload();            # Загрузка фотографии на сервер

    # Не советую тебе ничего не трогать впереди !!!
    class photoUpdater {
    	function __construct($token, $group, $timezone){
    		date_default_timezone_set($timezone);
    		$this->dir = __DIR__;
    		$this->file = $this->dir.'/photo.png';
    		$this->token = $token;
    		$this->group = $group;
    	}

      # Проверяем на наличие фотографии
    	public function checkExistsPhoto(){
				if(file_exists($this->file)) 
					echo "-> File with photo found! \r\n";
				else 
					exit("-> File with photo not found! \r\n");
    	}

      # Авторизация в Вконтакте
    	public function auth(){
				if(!isset($this->token) || !$this->token || $this->token == null || $this->token == false) 
					exit("-> Bad token signature \r\n");

				$get->request = $this->curl('https://api.vk.com/method/users.get', ['access_token' => $this->token], true);
				
    		if($get->request->error) exit("-> Invalid Access-Token: ".$get->request->error->error_msg."\r\n");
    		elseif($get->request->response[0]) echo "-> Logined as ".$get->request->response[0]->first_name." ".$get->request->response[0]->last_name." @id".$get->request->response[0]->uid." by ACCESS TOKEN \r\n";
    	}

      # Создаём резулат аватарки
    	public function createPhoto(){
				# Создаём изображение
				echo "-> Creating photo \n\r";
				$top = imagecreatefrompng($this->file);
				$size = getimagesize($this->file);
				$image = imagecreatefrompng($this->file);
				$color = imagecolorallocate($image, 253, 245, 230);
				imagecopyresampled($image, $top, 0, 0, 0, 0, $size[0], $size[1], $size[0], $size[1]);

				# Отрисовка
						imagettftext($image, 20, 0, 280, 25, $color, $this->dir.'/font1.ttf', 'LyntCore • Site News'); # Сайт
						imagettftext($image, 20, 0, 10, 30, $color, $this->dir.'/font.otf', date('H:i')); # Время
						imagettftext($image, 20, 0, 740, 30, $color, $this->dir.'/font.otf', date('d.m')); # Дата

						$max_right = '785';
						$cpu = 'CPU: '.sys_getloadavg()[0];
						$strlen = $max_right - strlen($cpu) * 6.2;
						imagettftext($image, 15, 0, $strlen, 195, $color, $this->dir.'/font.otf', $cpu); # Load Average
					
					# Сохранение результата
						imagepng($image, 'result.png') or exit("-> Error Saving Result... \n\r");
						echo "-> Photo Created \n\r"; imagedestroy($image);
			}

      # Загружаем фотографию на сервер
      public function upload(){
      	echo "-> Uploading on server \n\r";
      	$req->one = $this->curl('https://api.vk.com/method/photos.getOwnerCoverPhotoUploadServer', ['group_id' => $this->group, 'access_token' => $this->token], true);
      	if($req->one->error) exit("-> Error Get Upload Link".$req->one->error->error_msg."\n\r");
	      	else if($req->one->response){
	      		$get->upload = $this->curl($req->one->response->upload_url, ['photo' => $this->uploadtype($this->dir.'/result.png')], true);
	      		$req->result = $this->curl('https://api.vk.com/method/photos.saveOwnerCoverPhoto', [
	      			'hash' => $get->upload->hash, 
	      			'photo' => $get->upload->photo, 
	      			'access_token' => $this->token
	      		], true);

	      		if($req->result->response->images) echo "-> Cover successfully updated - Link: ".$req->result->response->images[2]->url."\n\r";
	      			elseif($req->result->error) exit("-> Error Saving Cover: ".$req->result->error->error_msg."\n\r");
	      	} unlink($this->dir.'/result.png');
      }

      # Используем CURL для отправки запросов
      public function curl($url, $post = false, $decode = false){
				$ch = curl_init($url);

				# Curl Default Settings
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; Auto-Shapka; +http://'.$_SERVER['HTTP_HOST'].'/)');

				if($post){ 
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				}

				# Working
				$response = curl_exec($ch); curl_close($ch);
				if($decode == true) 
					return json_decode($response);
				else
					return $response;
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
      	if($version >= '5.6') return new CURLFile($file);
     	 	elseif($version < '5.6') return '@'.$file;
      }
  }
