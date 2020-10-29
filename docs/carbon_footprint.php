<?php
    define("DB_server","localhost");
    define("DB_user","root");
    define("DB_password","toor33"); //toor33
    define("DB_name","phpmyadmin");
    function db_connect(){
        $connection = mysqli_connect(DB_server,DB_user,DB_password,DB_name);
        return $connection;
    };
    $db = db_connect();
    $distinctFruits= $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group = 'Fruits' ORDER BY food_name ASC";
    $fruitsQuery = mysqli_query($db,$distinctFruits);
    $fruitsArr = array();
    foreach ($fruitsQuery as $row) {
        $fruitsArr[] = $row;
    }

    $distinctDairy= $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group = 'Dairy and Eggs' ORDER BY food_name ASC";
    $dairyQuery = mysqli_query($db,$distinctDairy);
    $dairyArr = array();
    foreach ($dairyQuery as $row) {
        $dairyArr[] = $row;
    }

    $breakfastOther = $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group != 'Fruits' && food_group != 'Dairy and Eggs' ORDER BY food_name ASC";
    $breakfastQuery = mysqli_query($db,$breakfastOther);
    $breakfastArr = array();
    foreach ($breakfastQuery as $row) {
        $breakfastArr[] = $row;
    }

    $distinctVeges= $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group = 'Vegetables' ORDER BY food_name ASC";
    $vegeQuery = mysqli_query($db,$distinctVeges);
    $vegeArr = array();
    foreach ($vegeQuery as $row) {
        $vegeArr[] = $row;
    }

    $distinctMeat= $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group = 'Meats' ORDER BY food_name ASC";
    $meatQuery = mysqli_query($db,$distinctMeat);
    $meatArr = array();
    foreach ($meatQuery as $row) {
        $meatArr[] = $row;
    }

    $distinctOther= $sql="SELECT DISTINCT(food_name) FROM combined_data_removed WHERE food_group != 'Vegetables' && food_group != 'Meats' ORDER BY food_name ASC";
    $otherQuery = mysqli_query($db,$distinctOther);
    $otherArr = array();
    foreach ($otherQuery as $row) {
        $otherArr[] = $row;
    }



//query to get data from the table "combined_data"
    $totalSet = "SELECT * FROM combined_data_removed ORDER BY food_group ASC";
    //execute query
    $totalQuery = mysqli_query($db, $totalSet);
    //create an empty array
    $totalArr = array();
    //loop through the returned data
    foreach ($totalQuery as $row) {
        $totalArr[] = $row;
    }

    //free memory associated with result
    $totalQuery -> close();
    //close connection
    $db -> close();
?>

<!DOCTYPE HTML>
<!--
	Ion by TEMPLATED
	templated.co @templatedco
	Released for free under the Creative Commons Attribution 3.0 license (templated.co/license)
-->
<html>
	<head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		<title>What's Your Footprint? - Trailblazers</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <!-- Slideshow -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
		<script src="js/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
        <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
        <script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
        <script src="js/Chart.min.js"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">

        <script type ="text/javascript">
            $(document).ready(function(){
                $('.selectpicker').selectpicker({
                    style: 'btn-default',
                    size: false,
                    width: 'fit'
                });
            });

            $(function(){
                $("#down_button").click(function(){
                    $('html,body').animate(
                        {
                            scrollTop:$('#what_next').offset().top
                        },
                        'slow'
                    )
                });
            });
        </script>
        <script type ="text/javascript">
            var dataset = <?php echo json_encode($totalArr);?>;
            var food1, food2, food3, food4, food5, food6, food7, food8, food9;
            var food1_value, food2_value, food3_value, food4_value, food5_value, food6_value, food7_value, food8_value, food9_value;
            var data1=dataset;
            var breakfast = {};
            var lunch = {};
            var dinner = {};
            var myChart;
            var chartExist = false;

            function validateInput(){
                var food_arr = [];

                food1 = document.getElementById("food_name1").value;
                food2 = document.getElementById("food_name2").value;
                food3 = document.getElementById("food_name3").value;
                food4 = document.getElementById("food_name4").value;
                food5 = document.getElementById("food_name5").value;
                food6 = document.getElementById("food_name6").value;
                food7 = document.getElementById("food_name7").value;
                food8 = document.getElementById("food_name8").value;
                food9 = document.getElementById("food_name9").value;
                food_arr.push(food1,food2,food3,food4,food5,food6,food7,food8,food9);

                for(var i=0; i<food_arr.length; i++){
                    for (var j = 0; j < data1.length; j++) {
                        if(i>-1 && i<3){
                            if((food_arr[i] === data1[j].food_name) && (data1[j].nutrient === "sum_emission")){
                                if (!(food_arr[i] in breakfast)) {
                                    breakfast[food_arr[i]] = data1[j].value;
                                } else {
                                    var temp = parseFloat(breakfast[food_arr[i]]);
                                    breakfast[food_arr[i]] = temp + parseFloat(data1[j].value);
                                }
                            } //inner if
                        } else if (i>2 && i<6) {
                            if((food_arr[i] === data1[j].food_name) && (data1[j].nutrient === "sum_emission")){
                                if (!(food_arr[i] in lunch)) {
                                    lunch[food_arr[i]] = data1[j].value;
                                } else {
                                    var temp = parseFloat(lunch[food_arr[i]]);
                                    lunch[food_arr[i]] = temp + parseFloat(data1[j].value);
                                }
                            } //inner if
                        } else if (i>5 && i<9) {
                            if((food_arr[i] === data1[j].food_name) && (data1[j].nutrient === "sum_emission")){
                                if (!(food_arr[i] in dinner)) {
                                    dinner[food_arr[i]] = data1[j].value;
                                } else {
                                    var temp = parseFloat(dinner[food_arr[i]]);
                                    dinner[food_arr[i]] = temp + parseFloat(data1[j].value);
                                }
                            } //inner if
                        }
                    } // inner for loop
                } // end of for loop of food_arr
                if (chartExist===true) {myChart.destroy();}
                showfood();
            }


            function showfood(){
                var chart_x1=["Breakfast","Lunch","Dinner"];
                var breakfastName=[];
                var lunchName=[];
                var dinnerName=[];
                var breakfastSum = 0;
                var lunchSum = 0;
                var dinnerSum = 0;
                var mealTotal = [];

                for(var item in breakfast) {
                    breakfastName.push(item);
                    breakfastSum += parseFloat(breakfast[item]);
                }


                for(var item in lunch) {
                    lunchName.push(item);
                    lunchSum += parseFloat(lunch[item]);
                }

                for(var item in dinner) {
                    dinnerName.push(item);
                    dinnerSum += parseFloat(dinner[item]);
                }

                var totalFoodPrint = 0;
                mealTotal.push(breakfastSum,lunchSum, dinnerSum);
                for(var item in mealTotal){
                    item = Number(item).toFixed(2);
                }

                for(var i=0; i<mealTotal.length; i++){
                    totalFoodPrint += parseFloat(mealTotal[i]);
                }

                // make it 110g instead of 100g
                var totalPetrol = totalFoodPrint *1.1;

                totalFoodPrint = Number(totalFoodPrint).toFixed(2);
                totalPetrol = Number(totalPetrol / 0.118).toFixed(2);

                var ctx = document.getElementById('myChart').getContext('2d');
                var config={
                    type:'bar',
                    data:{
                        labels:chart_x1,
                        tooltipText: [breakfastName,lunchName,dinnerName],
                        datasets:[{
                                data:mealTotal,
                                backgroundColor: ['rgba(63,81,181,0.7)','rgba(76,175,80,0.7)','rgba(255,152,0,0.7)']
                                //hoverBackgroundColor: 'rgba(255,152,0,0.7)'
                            }

                        ]
                    },
                    options: {
                        title: {
                          display: true,
                          text: "Greenhouse Gas Emissions Produced by Meal"
                        },
                        legend: {
                            display: false
                        },
                        scales: {
                            xAxes: [{  }],
                            yAxes: [{
                                //stacked: true,
                                ticks : {
                                    min: 0,
                                    callback: function(value, index, values){
                                        return value + "kg";
                                    }
                                    //max:
                                }
                                //scaleLabel: {
                                //    display: true,
                                //    labelString: '' Y axis title
                                //}
                            }]
                        },
                        responsive: true,
                        tooltips: {
                            callbacks: {
                                title: function(tooltipItem, data) {
                                    var title = data.tooltipText[tooltipItem[0].index];
                                    return title;
                                },
                                label: function(tooltipItem, data){
                                    tooltipItem.yLabel = Number(tooltipItem.yLabel).toFixed(2);
                                    return tooltipItem.yLabel +'kg';
                                }
                            }
                        }
                    }
                }
                Chart.defaults.global.defaultFontSize = 14;
                myChart = new Chart(ctx, config);
                totalFoodPrint += "kg";
                totalPetrol += "km";
                document.getElementById("result_petrol").innerHTML = totalFoodPrint;
                document.getElementById("result_car").innerHTML = totalPetrol;
                document.getElementById("bar_chart_icon").style.display = "none";
                document.getElementById("petrol").style.display = "block";
                chartExist = true;
                //reset
                breakfast = {};
                lunch = {};
                dinner = {};
            }
        </script>
		<noscript>
			<link rel="stylesheet" href="css/skel.css" />
			<link rel="stylesheet" href="css/style.css" />
			<link rel="stylesheet" href="css/style-xlarge.css" />
		</noscript>
        <link rel="apple-touch-icon" sizes="180x180" href="/favicon_io/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon_io/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon_io/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="512x512" href="/favicon_io/android-chrome-512x512.png">
        <link rel="icon" type="image/png" sizes="192x192" href="/favicon_io/android-chrome-192x192.png">
        <link rel="manifest" href="/favicon_io/site.webmanifest">
        <link rel="mask-icon" href="/favicon_io/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#da532c">
        <meta name="theme-color" content="#ffffff">
	</head>
	<body id="top">

		<!-- Header -->
			<header id="header" class="skel-layers-fixed">
                <h5 class="team_logo"><a href="index.html">Trailblazers</a></h5>
				<nav id="nav">
					<ul>
						<li><a href="index.html">Home</a></li>
						<li><a href="carbon_footprint.php" class="active-page">What's Your Footprint?</a></li>
						<li><a href="meal_plan.php">Meal Planning</a></li>
                        <li><a href="recipes.php">Recipes</a></li>
                        <li><a href="facts.html" >Facts</a></li>
					</ul>
				</nav>
			</header>

            <div class="breadcrumb container">
                <a href="index.html">Home</a>&nbsp; >&nbsp;
                <span>What's Your Footprint?</span>
            </div>
        <!-- Banner -->
        <div class="container">
            <div id="mealBanner2">
                <br><br><br><br>
                <header class="major">
                    <h3 style="color:#ffffff; font-weight: bold;">What's Your Footprint?</h3>
                    <p style="color: #ffffff">Find out your current carbon footprint</p>
                </header>
            </div>
        </div>
		<!-- Main -->
			<div id="main" class="wrapper style1">
                <div class="container">
                <div class="row">
                    <div class="6u">
                        <p>What you eat is important to your carbon footprint. Carbon footprint is the quantity of greenhouse gas in carbon dioxide equivalent (CO2e) which is
                            generated across the supply chain of the product. <br><br>
                            Let's find out the carbon footprint based on your choice by entering what you eat for a day.&nbsp;<span class='glyphicon glyphicon-info-sign my-tooltip'
                                                                                                                              title="Each food item is calculated per 100g"></span>
                        </p>
                        <form method="post" id="multiple_select_form">

                        </form>
                            <h4 class="selectMenu" style="color: #000000;">BREAKFAST</h4>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name1">
                                <option data-tokens="">Select Fruits</option>
                                <?php
                                foreach($fruitsArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name2">
                                <option data-tokens="">Select Dairy</option>
                                <?php
                                foreach($dairyArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name3">
                                <option data-tokens="">Select Other</option>
                                <?php
                                foreach($breakfastArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <br><br>
                            <h4 class="selectMenu" style="color: #000000;">LUNCH</h4>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name4">
                                <option data-tokens="">Select Vegetables</option>
                                <?php
                                foreach($vegeArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name5">
                                <option data-tokens="">Select Meats</option>
                                <?php
                                foreach($meatArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name6">
                                <option data-tokens="">Select Other</option>
                                <?php
                                foreach($otherArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <br><br>
                            <h4 class="selectMenu" style="color: #000000;">DINNER</h4>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name7">
                                <option data-tokens="">Select Vegetables</option>
                                <?php
                                foreach($vegeArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name8">
                                <option data-tokens="">Select Meats</option>
                                <?php
                                foreach($meatArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="selectpicker" data-show-subtext="true" data-live-search="true" id="food_name9">
                                <option data-tokens="">Select Other</option>
                                <?php
                                foreach($otherArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option data-tokens='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                        <br><br>
                        <ul class="actions">
                            <li><a class="button special" onclick="validateInput()" id="findout">Find out your footprint</a></li>
                        </ul>
                    </div> <!-- first 6u -->
                    <div class="6u align-center" id="bar_chart_icon">
                        <br><br><br>
                        <img src="images/bar_chart_icon.png" width="150px"/></a><br><br>
                        <p>Click on the <span style="font-weight: bold;">FIND OUT YOUR FOOTPRINT</span>&nbsp;button<br>to see your footprint</p>
                    </div>
                    <div class="6u">
                        <canvas id="myChart" width="60" height="40"></canvas>
                        <div id="petrol" style="display: none;margin:0;"><br>
                            <img src="images/foot_icon.png" width="40"/></a><p style="display: inline-block">&nbsp;Your daily carbon footprint is &nbsp;</p><p id="result_petrol" style="display: inline-block; font-weight:bold;"></p><br>
                            <img src="images/car_icon.png" width="50"/></a><p style="display: inline-block">&nbsp;This is the equivalent of driving a medium petrol car&nbsp;</p><p id="result_car" style="display: inline-block; font-weight:bold;"></p><br>
                            <img src="images/ideal_amount.png" width="50"/></a><p style="display: inline-block">&nbsp;The ideal daily carbon footprint is &nbsp;</p><p style="display: inline-block; font-weight:bold;">1.1kg</p><br>
                            <div class="align-center" style="font-weight: bold;">
                            <button id="down_button" class="btn btn-warning btn-sm">
                                <span class="glyphicon glyphicon-menu-down"></span></button>&nbsp;&nbsp;Let's build your recipes!
                            </div>
                        </div>
                    </div>
                </div><br><br><br><br><br><br>
                    <div class="align-left" id="what_next"><!--row-->
                        <div class="row" style="padding: 0 5em 2em;">
                            <div class="6u">
                                <a style="font-size: 1.25em;color: #FD823E;display: inline-block" href="meal_plan.php">
                                    Do you want to create recipes?
                                </a>
                                <p style="margin:0;">Use our Footprint Calculator to find out how many trees are required to offset your meal's carbon footprint.</p>
                                <br>
                            </div>
                            <div class="6u">
                                <a style="font-size: 1.25em;color: #FD823E;" href="recipes.php">
                                    Do you want to see our recipes?
                                </a>
                                <p style="margin:0;">Show your care for the environment and communities across the World by offsetting your footprint with our carbon deficient meals.</p>
                            </div>
                        </div>
                    </div>
                </div><br><br><br> 	<!-- 1st Container -->
            </div> 	<!-- main wrapper -->

		<!-- Footer -->
			<footer id="footer">
				<div class="container">
					<ul class="copyright">
						<li>Copyright &copy; 2020 Trailblazers.</li>
                        <li>Design: TEMPLATED</li>
                        <li>Images: Pexels</li>
					</ul>
				</div>
			</footer>
	</body>
</html>