<?	$get = (object) $_GET;

	if(!$get->user) exit;
	if(get_headers($get->user)) $get->user = explode('/', $get->user)[3];

	$obj = apivk('users.get?fields=photo_100,sex,domain,verified&user_ids='.$get->user);
	if($obj->error || $obj == null) exit;

	if($obj->response[0]->deactivated)
		switch($obj->response[0]->deactivated){
			case 'banned': echo alert('warning', 'Страница заблокирована'); break;
			case 'deleted': echo alert('warning', 'Страница удалена'); break;
		}

	else {

		$info = (object) [
			'sex' => [1 => 'Зарегистрировалась', 2 => 'Зарегистрировался'],
			'verify' => [1 => '<span class="page_verified"></span>'],
			'reg' => getDateRed($obj->response[0]->uid)
		];

		echo '
			<div class="search_result">
				<div class="img">
					<a href="https://vk.com/'.$obj->response[0]->domain.'"><img class="search_item_img" src="'.$obj->response[0]->photo_100.'" alt="'.$obj->response[0]->first_name.' '.$obj->response[0]->last_name.'"></a>
				</div>
				<div class="info">
					<div class="labeled name"><a href="https://vk.com/'.$obj->response[0]->domain.'" target="_blank">'.$obj->response[0]->first_name.' '.$obj->response[0]->last_name.'</a> '.$info->verify[$obj->response[0]->verified].' </div>
					<div class="labeled"><strong>ID: </strong>'.$obj->response[0]->uid.'</div>
					<div class="labeled"><strong>'.$info->sex[$obj->response[0]->sex].' в соцсети: </strong>'.$info->reg.'</div>
				</div>
			</div>';
	}

	function alert($type, $text){
		switch($type){
			case 'info': return '<div class="alert alert-info"><p class="alert-message">'.$text.'</p></div>'; break;
			case 'warning': return '<div class="alert alert-warning"><p class="alert-message">'.$text.'</p></div>'; break;
		}
	}

	function getDateRed($id){
		$info = file_get_contents('http://vk.com/foaf.php?id='.$id);
		$pregm = '/<ya:created dc:date="([\\d]{4}-[\\d]{2}-[\\d]{2}T[\\d]{2}:[\\d]{2}:[\\d]{2}\\+[\\d]{2}:[\\d]{2})"/i';
		if(preg_match($pregm, $info, $matches)) $kastr = explode("T", $matches[1]); $time = trim($kastr[1]); 
		$time = str_replace(array('+03:00'), ' ', trim($time)); $time = preg_replace('/\+/', ' | +', $time);
		$time1 = explode(" +", $time); $kastr1 = explode("-", $kastr[0]); $kastr2 = explode(":", explode("+", $kastr[1])[0]);

		$monthes = array(
			'01' => 'Января', '02' => 'Февраля', '03' => 'Марта', '04' => 'Апреля', '05' => 'Мая',
			'06' => 'Июня', '07' => 'Июля', '08' => 'Августа', '09' => 'Сентября', '10' => 'Октября',
			'11' => 'Ноября', '12' => 'Декабря'
		);
		
		return $kastr1[2].' '.$monthes[$kastr1[1]].' '.$kastr1[0].' года в '.$kastr2[0].':'.$kastr2[1].' по Москве';
	}

	function apivk($link){
		$ch = curl_init('https://api.vk.com/method/'.$link);

	    # Curl Default Settings
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; LyntCore; +http://LyntCore.ru/)');

		# Working
		$response = curl_exec($ch); curl_close($ch);
		return json_decode($response);
	}