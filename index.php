<?php

require "/Applications/MAMP/htdocs/pars_wb/vendor/autoload.php";

use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;

use Facebook\WebDriver\Chrome\ChromeDriver;

// https://www.cyberithub.com/how-to-install-selenium-webdriver-for-php-in-9-easy-steps/

// Задача 1 
// Достать отзывы 

// Задача 2
// Разбить код на функции 

// 2.1 Подумать: Если программа не выполняется?  
// 2.2 Десять вниз, один вверх
// 2.3 Сделать процентный вывод результата  
// 2.4 Доработать цикл. Не нравится условие: пока 0 отзывово - перезагружай 

// Задача 3 
// Достать ссылки всех товаров магазина  ->  затем вытащить с каждого товара ссылку на отзывы 
// 3.1 Проверять загруженность станицы 


//        
// Получение ссылок на каждый продукт магазина 
//    
function get_urls_products($driver, $next_link, $conn, $id_shop) {
    $count_links = 0;

    // Достаем ссылки на продукты из БД 
    $sql = "SELECT * FROM products";
    $products = $conn -> query($sql);      
    $products_link = [];
    while($row = mysqli_fetch_assoc($products)) {
        array_push($products_link, $row['link']);
    }

    $driver -> get($next_link);
    sleep(5);
    echo 'Страница 1 ', PHP_EOL; // Вывод данных о текущей странице  "текущая/из" 
    
    // Достаем из БД ай-ди магазина по текущей ссылке 
    
    // Скроллинг вниз страницы   
    $pixels = 0;
    $time1 = time();
    while (time() - $time1 <= 5) {
        $pixels += 1000;
        $script = 'window.scrollTo(0, ' . $pixels . ')';
        $driver -> executeScript($script);
        sleep(0.5);
    }

    // Ссылка на следующую страницу магазина 
    $links = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::className('catalog-page')) -> 
    findElement(WebDriverBy::className('pagination')) -> findElement(WebDriverBy::className('pageToInsert')) -> findElements(WebDriverBy::tagName('a'));

    // Получение ссылок на страницы магазина 
    $links_prod = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::className('catalog-page')) -> 
    findElement(WebDriverBy::className('catalog-page__main')) -> findElement(WebDriverBy::className('catalog-page__content')) -> findElement(WebDriverBy::className('product-card-overflow')) -> 
    findElement(WebDriverBy::className('product-card-list')) -> findElements(WebDriverBy::tagName('article')); 

    // Добавление в массив ссылок на каждый продукт магазина 
    foreach ($links_prod as $l) {
        if (!in_array($l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'), $products_link)) {
            $link_product = $l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'); 
            $name_product = $l -> findElement(WebDriverBy::className('product-card__wrapper')) -> findElement(WebDriverBy::className('product-card__middle-wrap')) -> 
            findElement(WebDriverBy::className('product-card__brand-wrap')) -> findElement(WebDriverBy::className('product-card__name')) -> getText();
            $name_product = substr($name_product, 2);
            echo $name_product, PHP_EOL;
            echo $link_product, PHP_EOL;
            echo $id_shop, PHP_EOL;

            

            $sql = "INSERT INTO products (name, link, shop_id) VALUES ('" . $name_product . "', '" . $link_product . "', " . $id_shop . ");";
            
            echo $sql, PHP_EOL;
            $conn -> query($sql);
            
            echo 'Запсиана новая ссылка ' . $l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'), PHP_EOL;
            echo PHP_EOL;
        }
    }

    $count_links += count($links_prod); // Счетчик ссылок 
    
    // echo count($get_link), PHP_EOL;


    // Листание следующих страниц магазина и сбор с каждой из них ссылок на каждый товар 
    for ($i=1; $i < count($links); $i ++) {
        $sql = "SELECT * FROM products";
        $products = $conn -> query($sql);    
        $products_link = [];
        while($row = mysqli_fetch_assoc($products)) {
            array_push($products_link, $row['link']);
        }

        // Получение ссылки на следующую страницу и переход на нее 
        $all_pages = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::className('catalog-page')) -> 
        findElement(WebDriverBy::className('pagination')) -> findElement(WebDriverBy::className('pageToInsert')) -> findElements(WebDriverBy::tagName('a'));        
        $next_link = end($all_pages) -> getAttribute('href');
        
        $driver -> get($next_link);
        sleep(6);

        // Скроллинг вниз страницы 
        $time1 = time();
        while (time() - $time1 <= 5) { 
            $pixels += 1000;
            $script = 'window.scrollTo(0, ' . $pixels . ')';
            $driver -> executeScript($script);
            sleep(0.5);
        } 

        // Получение ссылок на товары с текущей страницы  
        $links_prod = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::className('catalog-page')) -> 
        findElement(WebDriverBy::className('catalog-page__main')) -> findElement(WebDriverBy::className('catalog-page__content')) -> findElement(WebDriverBy::className('product-card-overflow')) -> 
        findElement(WebDriverBy::className('product-card-list')) -> findElements(WebDriverBy::tagName('article')); 

        // Добавление в массив ссылок на каждый продукт магазина 
        foreach ($links_prod as $l) {
            if (!in_array($l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'), $products_link)) {
                $link_product = $l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'); 
                $name_product = $l -> findElement(WebDriverBy::className('product-card__wrapper')) -> findElement(WebDriverBy::className('product-card__middle-wrap')) -> 
                findElement(WebDriverBy::className('product-card__brand-wrap')) -> findElement(WebDriverBy::className('product-card__name')) -> getText();
                $name_product = substr($name_product, 2);

                // echo $name_product, PHP_EOL;
                // echo $link_product, PHP_EOL;
                // echo $id_shop, PHP_EOL;
    
                $sql = "INSERT INTO products (name, link, shop_id) VALUES ('" . $name_product . "', '" . $link_product . "', " . $id_shop . ");";
                // echo $sql, PHP_EOL;
                $conn -> query($sql);
                
                echo 'Запсиана новая ссылка ' . $l -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href'), PHP_EOL;
                echo PHP_EOL;
            }
        }

        $count_links += count($links_prod); // Прибавляю к счетчику ссылок 
        echo 'Страница ' . strval($i + 1), PHP_EOL; // Вывод данных о текущей странице  "текущая/из"
        // echo count($get_link), PHP_EOL;
    } 
    
    // Вовзращаем количество ссылок, присутствующих в магазине 
    return $count_links;
}


// 
// Функция для получения страницы с отзывами товара 
// Получает: драйвер, ссылку на карточку продукта 
// Возвращает: ссылку (при наличии отзывов), сообщение (при отсутствии отзывов)    
function  click_link_to_feedbacks ($driver, $product_url) {

    // Перезагрузка страницы до тех пор, пока программа не получит более 0 отзывов (Средство от защиты Вайлдбериз )
    $number = 0;
    $status = 1;
    $count = 0;

    while ($number == 0) {
        $driver -> get($product_url);
        sleep(5);

        $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::tagName('div')) -> 
        findElement(WebDriverBy::className('product-page')) -> findElement(WebDriverBy::className('product-page__grid')) -> findElement(WebDriverBy::className('product-page__header-wrap')) -> 
        findElement(WebDriverBy::className('product-page__common-info')) -> findElement(WebDriverBy::tagName('a')) -> click();  

        sleep(5);


        $number = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::className('product-page')) -> 
        findElement(WebDriverBy::className('product-page__carousel-wrap')) -> findElement(WebDriverBy::className('product-page__user-activity')) 
        -> findElement(WebDriverBy::className('user-activity__tabs-wrap')) -> findElement(WebDriverBy::tagName('ul')) -> findElement(WebDriverBy::className('user-activity__tab')) -> 
        findElement(WebDriverBy::className('user-activity__link')) -> findElement(WebDriverBy::tagName('span')) -> getText(); 

        $count += 1;
        
        // Если отзывов после 7 загрузок страницы 0 отзывов - скорее всего, отзывов нет 
        if ($count == 3) {
            $status = 0;
            break;
        }

    }
    if ($status == 1) {
        $link_to_feedbacks = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::tagName('div')) -> 
        findElement(WebDriverBy::className('product-page')) -> findElement(WebDriverBy::className('product-page__carousel-wrap')) -> findElement(WebDriverBy::className('product-page__user-activity')) -> 
        findElement(WebDriverBy::className('comments')) -> findElement(WebDriverBy::tagName('div')) -> findElement(WebDriverBy::className('comments__content')) -> 
        findElement(WebDriverBy::className('comments__sorting-wrap')) -> findElement(WebDriverBy::tagName('a')) -> getAttribute('href');

        return 'https://www.wildberries.ru' . $link_to_feedbacks;
    } else {
        return 'Отзывов нет';
    }


} 

// 
// get_pars
// Функция, возвращающая количество комментариев на странице 
function get_number_feedbacks($wbUrl, $driver) {

    $quantity = '0';
    while ($quantity == '0')
        {
        $driver -> get($wbUrl);
        sleep(4);

        $quantity = $driver->findElement(WebDriverBy::tagName("main")) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::id('app')) -> 
        findElement(WebDriverBy::className('product-feedbacks')) -> findElement(WebDriverBy::className('product-feedbacks__wrapper')) -> 
        findElement(WebDriverBy::className('product-feedbacks__title')) -> findElement(WebDriverBy::className('product-feedbacks__count')) -> getText(); 

        // print_r($quantity . '; ');
        }
        sleep(3);
    
    return $quantity;
}

// 
// get_feedbacks 
// Функция, возвращающая комментарии со страницы по ссылке 
function get_feedbacks($driver, $quantity, $product_id, $conn) {

    $number_feedbacks = 0;  
    $pixels = 0;         
    while ($number_feedbacks < $quantity -1) {  

        // Scroll to down page  
        $pixels += 1000;
        $script = 'window.scrollTo(0, ' . $pixels . ')';
        $driver -> executeScript($script);

        // get feedbacks and number feedbacks 
        $feedbacks = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> findElement(WebDriverBy::id('app')) -> 
        findElement(WebDriverBy::className('product-feedbacks')) -> findElement(WebDriverBy::className('product-feedbacks__wrapper')) -> findElement(WebDriverBy::className('product-feedbacks__main')) -> 
        findElement(WebDriverBy::className('comments__list')) -> findElements(WebDriverBy::className('comments__item')); 
            
        $number_feedbacks = count($feedbacks);

        sleep(0.5);
    }

    // $list_of_feedbacks = [[], [], []];

    foreach ($feedbacks as $feed) {
        $sql = 'SELECT * FROM reviews';
        $reviews = $conn -> query($sql);
        $reviews_list = [];
        while ($row = mysqli_fetch_assoc($reviews)) {
            array_push($reviews_list, [$row['name_user'], $row['date'], $row ['text'], $row['product_id']]);

            // echo $row['date'] . ' ' . $row['product_id'], PHP_EOL;
        }

        $now_review = [];
        $name_review = ($feed -> findElement(WebDriverBy:: className('feedback__top-wrap')) -> findElement(WebDriverBy:: className('feedback__info')) ->
        findElement(WebDriverBy::className('feedback__header')) -> getText());
        $date_review = ($feed -> findElement(WebDriverBy:: className('feedback__top-wrap')) -> findElement(WebDriverBy:: className('feedback__info')) ->
        findElement(WebDriverBy::className('feedback__date')) -> getText()); 
        $text_review = ($feed -> findElement(WebDriverBy::className('feedback__content')) -> findElement(WebDriverBy::className('feedback__text')) -> getText());

        array_push($now_review, $name_review, $date_review, $text_review, $product_id);

        // print_r($now_review);

        if ((!in_array($now_review, $reviews_list))) {
            $sql = "INSERT INTO reviews (name_user, date, text, product_id) VALUES ('$name_review', '$date_review', '$text_review', '$product_id')";
            $conn -> query($sql); 

            $sql = 'SELECT * FROM reviews';
            $reviews = $conn -> query($sql);
            $reviews_list = [];
            while ($row = mysqli_fetch_assoc($reviews)) {
                array_push($reviews_list, [$row['name_user'], $row['date'], $row ['text'], $row['product_id']]);
            }     
        } 
    }


    // return $list_of_feedbacks;
// print_r($feedbacks);
// print_r(count($feedbacks));
}

function write_shops_to_db ($driver, $urls_shops, $conn) {

    // Вытаскиваем из БД ссылки на магазины 
    $sql = 'SELECT * FROM shops';
    $result = $conn -> query($sql);
    $shops_link = [];
    while($row = mysqli_fetch_assoc($result)) {
        array_push($shops_link, $row["link"]);
        // echo $row["link"], PHP_EOL;
    }

    foreach ($urls_shops as $link) {
        if (!in_array($link, $shops_link)) {
            $driver -> get($link);
            sleep(5);

            $name_shop = $driver -> findElement(WebDriverBy::tagName('main')) -> findElement(WebDriverBy::className('main__container')) -> 
            findElement(WebDriverBy::tagName('div')) -> findElement(WebDriverBy::className('catalog-page')) -> findElement(WebDriverBy::className('catalog-page__seller-details')) -> 
            findElement(WebDriverBy::className('seller-details')) -> findElement(WebDriverBy::className('seller-details__info-wrap')) -> findElement(WebDriverBy::className('seller-details__info')) -> 
            findElement(WebDriverBy::className('seller-details__title-wrap')) -> findElement(WebDriverBy::tagName('h2')) -> getText();

            // echo $name_shop, PHP_EOL;

            $sql = "INSERT INTO shops (name, link) VALUES ('" . $name_shop . "', '" . $link . "');";

            echo $sql, PHP_EOL;
            $conn -> query($sql);

        }
    }
    return $shops_link;
}


try {
    $serverUrl = "http://localhost:4443"; // переменная, хранящая адрес сервера на котором работает драйвер 
    $driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome()); // Создание сессии на локал хосте 

    // Соединение с БД 
    // Команда для просмотра рабочего порта БД: show variables like 'port';
    //https://www.google.com/search?q=%D0%BE%D1%88%D0%B8%D0%B1%D0%BA%D0%B0+1045+msql+%D0%BA%D0%B0%D0%BA+%D0%B8%D1%81%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D1%82%D1%8C+%D0%BD%D0%B0+%D0%BC%D0%B0%D0%BA+%D0%BE%D1%81+&sxsrf=AB5stBhOxjlhdiza5R_ntFUHv_WPQoz46g%3A1689835238167&ei=5ta4ZOPwCbm7wPAPj5yW6Ak&ved=0ahUKEwjj--Xo1pyAAxW5HRAIHQ-OBZ0Q4dUDCA8&uact=5&oq=%D0%BE%D1%88%D0%B8%D0%B1%D0%BA%D0%B0+1045+msql+%D0%BA%D0%B0%D0%BA+%D0%B8%D1%81%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D1%82%D1%8C+%D0%BD%D0%B0+%D0%BC%D0%B0%D0%BA+%D0%BE%D1%81+&gs_lp=Egxnd3Mtd2l6LXNlcnAiQtC-0YjQuNCx0LrQsCAxMDQ1IG1zcWwg0LrQsNC6INC40YHQv9GA0LDQstC40YLRjCDQvdCwINC80LDQuiDQvtGBIDIFEAAYogQyBRAAGKIEMgUQABiiBDIFEAAYogQyBRAAGKIESI0cUJMDWLUbcAN4AZABAJgBtgGgAfkMqgEEMC4xMrgBA8gBAPgBAcICChAAGEcY1gQYsAPCAgkQIRigARgKGCrCAgcQIRigARgKwgIIECEYFhgeGB3iAwQYACBBiAYBkAYI&sclient=gws-wiz-serp#fpstate=ive&vld=cid:4e330775,vid:Us3LC7f5Vek
    $server_name = "localhost:3306";
    $user_name = "root";
    $password = "11111111";
    $name_bd = 'pars_wb';

    $conn = new mysqli($server_name, $user_name, $password, $name_bd);

    if ($conn -> connect_error) {
        die("connection failed " . $conn->connect_error);
    } else {
        echo "connection sucsessfull", PHP_EOL;
    }

    $urls_shops = ['https://www.wildberries.ru/seller/691295']; // Массив с магазинами для анализа 
    
    // Запись в БД магазинов, в случае их отсутствия 
    $shops_link = write_shops_to_db($driver, $urls_shops, $conn); 

    // Первый шаг. Получение ссылок на товары магазина и их запись в бд, если их там нет  
    foreach ($shops_link as $url_shop) {
        // echo $url_shop, PHP_EOL;
        $sql = "SELECT id_shop FROM shops WHERE link = '" . $url_shop . "';";
        $res = $conn -> query($sql);  
        foreach($res as $r) {
            $id_shop = $r['id_shop'];
        }
        $received_link = get_urls_products($driver, $url_shop, $conn, $id_shop);
        echo 'Ссылок на Товары получено: ' . $received_link, PHP_EOL;
    }

    $sql = 'SELECT link FROM products';
    $products = $conn -> query($sql);    
    $products_link = [];
    while($row = mysqli_fetch_assoc($products)) {
        array_push($products_link, $row['link']);
    }

    // Получение ссылки на отзывы конкретного товара 
    foreach ($products_link as $link) {

        $link_to_feedbacks = click_link_to_feedbacks($driver, $link); 
        
        $sql = "SELECT id_product FROM products WHERE link = '" . $link . "';";
        $product_id = $conn -> query($sql);
        $product_id = mysqli_fetch_assoc($product_id)['id_product'];

        echo PHP_EOL;
        echo 'Новая ссылка ' . $link . ' товар номер ' . $product_id, PHP_EOL;

        if ($link_to_feedbacks != 'Отзывов нет') {
            // Получение количества отзывов на конкретный товар магазина 
            $quantity = get_number_feedbacks($link_to_feedbacks, $driver); 

            // echo $quantity, PHP_EOL;

            // Получение отзывов конкретного товара 
            $list_of_feedbacks = get_feedbacks($driver, $quantity, $product_id, $conn);

        } else {
            echo $link_to_feedbacks, PHP_EOL;
        }
    
    }

    // Array
    // (
    //     [0] => Оксана
    //     [1] => Сегодня, 13:11
    //     [2] => Хороший, очень тёплый
    //     [3] => 1
    // )

    // Array
    // (
    //     [0] => Оксана
    //     [1] => Сегодня, 13:11
    //     [2] => Хороший, очень тёплый
    //     [3] => 1
    // )

    // $sql = 'SELECT * FROM reviews';
    // $reviews = $conn -> query($sql);
    // $reviews_list = [];
    // while ($row = mysqli_fetch_assoc($reviews)) {
    //     array_push($reviews_list, [$row['name_user'], $row['date'], $row ['text'], $row['product_id']]);

    //     // echo $row['date'] . ' ' . $row['product_id'], PHP_EOL;
    // }

    // print_r($reviews_list[0]);

    




} catch (Exception $ex) {

    echo 'Код не сработал: ', $ex -> getMessage(), PHP_EOL; 

} finally {
    $conn -> close();
    $driver -> quit();
}






