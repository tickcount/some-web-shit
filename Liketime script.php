<?
	/*
		Скрипт Лайк-Тайма в группу
		Создатель: [
			Danil Somerholder / vk.com/id92774
			Ivan Salvatore / vk.com/s9008
		]

		Последнее обновление: 23.04.2017
		Просьба: Скрипт размещать с указанием автора, не изменять содержимое скрипта. Уважайте разработчика.
		Официально скрипт размещается на сайте LyntCore.com
		Алгоритм работы скрипта: [
			1. Сбор тех, кто лайкнул, репостнул, оставил комментарий и помещение всех id в массив.
			2. Случаный выбор человека из массива и получение его данных.
			3. Публикация поста и обновление статуса
		]
	*/

	# Настройка Лайк Тайма
	$liketime = new liketime([
		'group' => 126796920, # ID Группы без знака `-`
		'access_token' => 'acc', # Ключ доступа с правами к группе (ACCESS_TOKEN)
		'pinned' => 0, # Если в группе есть закреп, то значение 1, если нет, то 0
		'timeout' => 1 # Задержка между постами в минутах
	]);


	# LikeTime Init.
	$liketime->calculations();	# Подсчёт
	$liketime->winner();		# Выбор победителя
	$liketime->post();			# Постинг


	# Не советую ничего тут трогать
	# Но если хотите, то это ваше право :<
	class liketime {

		function __construct($settings){
			$settings = (object) $settings;
			$this->group = $settings->group;
			$this->access_token = $settings->access_token;
			$this->pinned = $settings->pinned;
			$this->timeout = $settings->timeout;
		}

		public function calculations(){
			$request = $this->apivk('execute', ['code' => '
				var wall = API.wall.get({"owner_id":"-'.$this->group.'", "count":2})["items"]['.$this->pinned.'];
				var likes = API.likes.getList({"type":"post", "owner_id":"-'.$this->group.'", "item_id":wall["id"], "filter":"likes", "count":1000})["items"];
				var copies = API.likes.getList({"type":"post", "owner_id":"-'.$this->group.'", "item_id":wall["id"], "filter":"copies", "count":1000})["items"];
				var comments = API.wall.getComments({"owner_id":"-'.$this->group.'","post_id":wall["id"], "count":2})["items"];
				var time = API.utils.getServerTime({}); return {"likes":likes, "copies":copies, "comments":comments, "wall":wall, "time":time};
			']);

			# Если ошибка, то завершаем работу скрипта
			if($request->error) die($request->error->error_msg);

			$comments = [];
			foreach ($request->response->comments as $user) {
				if($user->from_id > 0) $comments[] = $user->from_id;
			}

			$this->tickets = array_merge($request->response->likes, $request->response->copies, $comments); # Получение билетов за счёт склеивания массивов
			$this->winner = $this->tickets[array_rand($this->tickets)]; # Выбираем рандомного победителя
			$this->last_post = round(($request->response->time - $request->response->wall->date) / 60, 0); # Получаем информацию о последнем посте
		}

		public function winner(){
			$request = $this->apivk('execute', ['code' => '
				var last_name_gen = API.users.get({"lang":"ru", "user_ids":'.$this->winner.', "name_case":"gen"})[0]["first_name"];
				var last_name = API.users.get({"lang":"ru", "user_ids":'.$this->winner.'})[0];
				var photo = API.photos.get({"owner_id":'.$this->winner.', "album_id":"profile", "rev":1, "extended":1, "count":1})["items"][0];
				return {"last_name_gen":last_name_gen, "last_name":last_name["first_name"]+" "+last_name["last_name"], "photo":photo};
			']);

			$this->winner = (object) [
				'id' => $request->response->photo->id,
				'owner_id' => $request->response->photo->owner_id,
				'likes' => $request->response->photo->likes->count,
				'name' => $request->response->last_name,
				'name_gen' => $request->response->last_name_gen,
				'tickets' => array_keys($this->tickets, $request->response->photo->owner_id)
			];
		}

		public function post(){
			if(!$this->winner->owner_id) die($this->status('⏩ Ожидание лайков на последнем посте'));
			if($this->last_post >= $this->timeout){
				$this->apivk('wall.post', ['owner_id' => '-'.$this->group, 'from_group' => 1, 'attachments' => 'photo'.$this->winner->owner_id.'_'.$this->winner->id, 'message' => "
					📌 @club".$this->group." (Лайк-Тайм) • ".date("d/m/o")." 🔍
					⏩ Победитель - @id".$this->winner->owner_id." (".$this->winner->name.") ⏪
					📦 Сейчас на аватаре у @id".$this->winner->owner_id." (".$this->winner->name_gen."): ".$this->formating($this->winner->likes)." ❤
					💡 Шанс на победу: ".round(count($this->winner->tickets) * 100 / count($this->tickets), 2)."% 🏆
					✏ Теги: #ЛТ #ЛайкТайм #LT #LikeTime #Лайки #Лайк #Likes #Like #Photo #Cool
				"]); $this->status('⏩ Следующий пост через '.$this->word($this->timeout, 'минуту', 'минута', 'минуты', 'минут'));
			} else $this->status('⏩ Следующий пост через '.$this->word(($this->timeout - $this->last_post), 'минуту', 'минута', 'минуты', 'минут'));
		}

		public function status($text){
			$this->apivk('status.set', ['text' => $text, 'group_id' => $this->group]);
		}

		public function word($n, $s1, $s2, $s3, $b = false){
		    $m = $n % 10; $j = $n % 100;
		    if($b) $n = $n;
		    if($m == 0 || $m >= 5 || ($j >= 10 && $j <= 20)) return $n.' '.$s3;
		    if($m >= 2 && $m <= 4 ) return  $n.' '.$s2;
		    return $n.' '.$s1;
		}

		public function formating($num) {
			# Форматирование чисел
			# By Somerholder
			if(floor($num / 1000000) != false) return str_replace('.'.str_repeat(0, 2), false, number_format($num / 1000000, 2)).'M';
				elseif(floor($num / 1000) != false) return str_replace('.'.str_repeat(0, 2), false, number_format($num / 1000, 2)).'K';
					else return (int) $num;
		}

		public function apivk($method, $params = []){
			$ch = curl_init('https://api.vk.com/method/'.$method);

	        # Curl Default Settings
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($params, ['v' => '5.62', 'access_token' => $this->access_token]));
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; LikeTime; +http://'.$_SERVER['HTTP_HOST'].'/)');

	        # Creating response and closing curl
			$json = json_decode(curl_exec($ch)); curl_close($ch);
			if($json->error) return (object) ['error' => $json->error];
				elseif($json == null || $json->response == false) return (object) ['error' => ['error_code' => 1, 'error_msg' => 'Unknown Error']];
					elseif($json->response) return $json;
		}
	}