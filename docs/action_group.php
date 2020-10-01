<?php
define("DB_server","localhost");
define("DB_user","root");
define("DB_password",""); //toor33
define("DB_name","phpmyadmin");

$connect = new PDO("mysql:host=localhost; dbname=phpmyadmin;", "root", "");//toor33

function fill_select_box($connect, $food_group)
{
    $query = "SELECT DISTINCT(food_name) FROM combined_data WHERE food_group ='".$food_group."' ORDER BY food_name";

    $statement = $connect->prepare($query);

    $statement->execute();

    $result = $statement->fetchAll();

    $output = '';

    foreach($result as $row)
    {
        $output .= '<option value="'.$row["food_name"].'">'.$row["food_name"].'</option>';
    }

    return $output;
}

echo fill_select_box($connect,$_POST["food_group"]);

?>