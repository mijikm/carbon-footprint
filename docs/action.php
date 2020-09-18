<?php
    define("DB_server","localhost");
    define("DB_user","root");
    define("DB_password",""); //toor33
    define("DB_name","phpmyadmin");
    $conn = mysqli_connect(DB_server,DB_user,DB_password,DB_name);

    $output='';
    $sql="SELECT DISTINCT(food_name) FROM combined_data WHERE food_group ='".$_POST[foodGroup]."' ORDER BY food_name";
    $result=mysqli_query($conn,$sql);
    $output .= '<option value="" disabled selected>Select</option>';
    while($row=mysqli_fetch_array($result)){
        $output .='<option value="'.$row["food_name"].'">'.$row["food_name"].'</option>';
    }
    echo $output;
?>
