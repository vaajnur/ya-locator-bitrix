<?

$api_key = 'AHpH0VgBAAAA8FcHXwIA7b8-WHvp5NDvQe08RTwA0cxkgs4AAAAAAAAAAADyJ-gsqxw5zvtMFdteIJKXOxX8LA==';
$api = new \Yandex\Locator\Api($api_key);
// Определение местоположения по IP

function getIPYou(){
    if (!empty($_SERVER['HTTP_CLIENT_IP'])){
        //check ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //to check ip is pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
//    return '95.78.48.207';
    return $ip;
    
}

$api->setIp(getIPYou());

$error = false;
try {
    $api->load();
} catch (\Yandex\Locator\Exception\CurlError $ex) {
    $error = true;
    // file_put_contents('YA.log' , var_export($ex, true));
    // ошибка curl
} catch (\Yandex\Locator\Exception\ServerError $ex) {
    $error = true;
    // file_put_contents('YA.log' , var_export($ex, true));
    // ошибка Яндекса
} catch (\Yandex\Locator\Exception $ex) {
    $error = true;
    // какая-то другая ошибка (запроса, например)
    // file_put_contents('YA.log' , var_export($ex, true));
}

if($error == false){
    $response = $api->getResponse();
    // как определено положение
    $response->getType();
    // широта
    $response->getLatitude();
    // долгота
    $response->getLongitude();

    $longLat = $response->getLongitude().','.$response->getLatitude();

    // сериализация/десереализация объекта
    // echo "<pre>";
    // var_dump(unserialize(serialize($response)));
    // echo "</pre>";



    $ya_loc_city = geokoder($longLat);
    //echo "<pre>";
    // print_r($ya_loc_city);
    //echo "</pre>";
}

function geokoder($arCoo){

            $geoCoder = 'https://geocode-maps.yandex.ru/1.x/';
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$geoCoder);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_HEADER,0);
            curl_setopt($ch,CURLOPT_POSTFIELDS,"geocode=$arCoo&format=json&results=1&kind=locality");
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_TIMEOUT,60);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
            $geoCodeJson = json_decode(curl_exec($ch));
            curl_close($ch);
            $arLocSimple = array();
            if ($geoCodeJson->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->Components):
                $listLoc = $geoCodeJson->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address->Components;
                foreach($listLoc as $obj):
                    if ($obj->kind=='country'):
                        $arLocSimple['COUNTRY']=$obj->name;
                    endif;
                    if ($obj->kind=='locality'):
                        $arLocSimple['LOCALITY']=$obj->name;
                    endif;
                endforeach;
            endif;
            if ($arLocSimple['COUNTRY'] && $arLocSimple['LOCALITY']){
               $returnCity = $arLocSimple;
            }
	return $returnCity;
}
