<?php
    define("DB_server","localhost");
    define("DB_user","root");
    define("DB_password",""); //toor33
    define("DB_name","phpmyadmin");
    function db_connect(){
        $connection = mysqli_connect(DB_server,DB_user,DB_password,DB_name);
        return $connection;
    };
    $db = db_connect();
    $distinctSet = $sql="SELECT DISTINCT(food_name) FROM combined_data_removed ORDER BY food_name ASC"; //"SELECT Distinct(Food_product) FROM Food_emission ORDER BY Food_product ASC";
    $distinctQuery = mysqli_query($db,$distinctSet);
    $distinctArr = array();
    foreach ($distinctQuery as $row) {
        $distinctArr[] = $row;
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
    $distinctQuery -> close();
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
		<title>Carbon Footprint - Trailblazers</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <!-- Slideshow -->
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
		<script src="js/jquery.min.js"></script>
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
        <script src=https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
        <script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
        <script src="js/Chart.min.js"></script>
        <script type ="text/javascript">
            $(document).ready(function(){
                $(".chosen").chosen();
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
                console.log(breakfast);
                console.log(lunch);
                console.log(dinner);
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
						<li><a href="carbon_footprint.php" class="active-page">Carbon Footprint</a></li>
						<li><a href="meal_plan.php">Meal Planning</a></li>
                        <li><a href="facts.html" >Facts</a></li>
                        <li><a href="about_us.html">About Us</a></li>
					</ul>
				</nav>
			</header>

            <div class="breadcrumb container">
                <a href="index.html">Home</a>&nbsp; >&nbsp;
                <span>Carbon Footprint</span>
            </div>
        <!-- Banner -->
        <div class="container">
            <div id="mealBanner2">
                <br><br><br><br>
                <header class="major">
                    <h3 style="color:#ffffff; font-weight: bold;">Carbon Footprint</h3>
                    <p style="color: #ffffff">Where do emissions from food come from?</p>
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
                            Find out the carbon footprint of your food by entering what you eat for a day.
                            <!--* Negative value can be observed when the carbon dioxide absorbed by the plant’s photosynthesis is more than that released by its respiration.-->
                        </p>
                            <h5 class="selectMenu">Breakfast</h5>
                            <select class="chosen" id="food_name1">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                                </select>
                            <select class="chosen" id="food_name2">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="chosen" id="food_name3">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <br><br>
                            <h5 class="selectMenu">Lunch</h5>
                            <select class="chosen" id="food_name4">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="chosen" id="food_name5">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="chosen" id="food_name6">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <br><br>
                            <h5 class="selectMenu">Dinner</h5>
                            <select class="chosen" id="food_name7">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="chosen" id="food_name8">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                            <select class="chosen" id="food_name9">
                                <option value="" disabled selected>Select</option>
                                <?php
                                foreach($distinctArr as $row) {
                                    $food_name = $row['food_name'];
                                    echo "<option value='$food_name'>$food_name</option>";
                                }
                                ?>
                            </select>
                        <br><br><br>
                        <ul class="actions">
                            <li><a class="button alt" onclick="validateInput()">Find out your footprint</a></li>
                        </ul>

                    </div> <!-- first 6u -->
                     <div class="6u">
                        <div class="row">
                            <canvas id="myChart" width="60" height="40"></canvas>
                            <div id="petrol" style="display: none;"><br>
                                <p style="display: inline-block">Your daily carbon footprint is &nbsp;</p><p id="result_petrol" style="display: inline-block; font-weight:bold;"></p>
                                <p style="display: inline-block">This is the equivalent of driving a medium petrol car&nbsp;</p><p id="result_car" style="display: inline-block; font-weight:bold;"></p>

                            </div>
                        </div>
                    </div> <!-- 2nd 6u -->
                </div> <!-- 1st row -->

                <hr class="major" />
                    <div class ="12u align-center carbonBanner">
                        <a href="meal_plan.php" class="image effect"><img src="images/carbon_banner.png" width="500" alt="Meal Planning" /></a>
                        <div class="centered">
                            <a style="color: #ffffff" href="meal_plan.php">Let's build low foodprint recipes</a>
                        </div>
                    </div>
                </div> 	<!-- 1st Container -->

            </div> 	<!-- main wrapper -->

        <!-- Banner <div class ="12u align-center">
                <a href="meal_plan.php" class="image effect"><img src="images/nutrient.png" height="200" alt="Meal Planning" /></a>
                <p>Build your low carbon footprint recipes</p>
            </div>-->


                <!--
                <div class="row">
                    <div class ="6u">
                        <h3>TOP FOODS HIGHEST IN SELECTED NUTRIENT</h3>
                        <p>Research suggests that some packaged foods generate lower carbon footprint than
                            organic foods across the food supply chain. Hence, it is important to know the nutrient
                            density of the selected foods to reduce the individual carbon footprint
                            as well as eat the right nutrients. </p>
                        <h5>Choose nutrient&nbsp;&nbsp;&nbsp;&nbsp;</h5>
                        <div>
                            <select id="nutrient" onchange="filterNutrient()">
                                <option value="Select">Select</option>
                                <option value="Calcium_mg">Calcium</option>
                                <option value="Carb_g">Carb</option>
                                <option value="Fat_g">Fat</option>
                                <option value="Fiber_g">Fiber</option>
                                <option value="Protein_g">Protein</option>
                                <option value="Sugar_g">Sugar</option>
                                <option value="VitA_mcg">Vitamin A</option>
                                <option value="VitC_mg">Vitamin C</option>
                                <option value="VitE_mg">Vitamin E</option>
                            </select>
                        </div>
                        <br>

                        <br>
                        <ul class="actions">
                            <li><a class="button alt" onclick="validateInput2()">Submit</a></li>
                        </ul>
                    </div>
                    <div class="6u">
                        <canvas id="myChart2" width="100" height="60"></canvas>
                    </div>
                    <br><br>
                </div> -->
                    <!--
                    <script type ="text/javascript">
                        var original_data = ;
                        var food_data = original_data;
                        var data_filter = "";
                        var select_nutrient = "";
                        var myChart2;
                        var chartExist2 = false;
                        var topLimit;

                        function filterNutrient() {
                            select_nutrient = document.getElementById("nutrient").value;
                        }

                        function validateInput2() {
                            if (select_nutrient.length > 0 && document.getElementById("nutrient").value != "Select" &&
                                chartExist2 === true)  {
                                myChart2.destroy();
                                food_data = food_data.filter(d => d.nutrient === select_nutrient);

                                showFood2();
                            } else if (select_nutrient.length > 0 && document.getElementById("nutrient").value != "Select") {
                                food_data = food_data.filter(d => d.nutrient === select_nutrient);

                                showFood2();
                            }
                        }

                        function showFood2() {
                            // descending order
                            food_data.sort(function(a, b) {
                                return b.value - a.value;
                            });

                            topLimit = 10;
                            for (var i=0; i<10;i++){
                                if (food_data[i].value === '0') {
                                    topLimit = i;
                                    break;
                                }
                            }

                            var top_ten = food_data.slice(0,topLimit);
                            var top_emission = [];
                            for (var i in top_ten) {
                                for(var j=0; j<original_data.length;j++) {
                                    //console.log(dataset[j]);
                                    if(top_ten[i].food_name === original_data[j].food_name &&
                                    original_data[j].nutrient === "sum_emission") {
                                        top_emission.push(original_data[j].value);
                                    } else {continue;}
                                }
                            }
                            //console.log(top_emission);
                            var chart_x = [];
                            var description_x = [];
                            var chart_y = [];
                            var splitNutrient = select_nutrient.substr(0,select_nutrient.indexOf('_'));
                            var splitUnit = select_nutrient.split("_").pop();

                            for(var i in top_ten) {
                                description_x.push(top_ten[i].descrip);
                                var splitString = top_ten[i].food_name
                                chart_x.push(splitString); // get food name
                                chart_y.push(top_ten[i].value);
                            }

                            var ctx2 = document.getElementById('myChart2').getContext('2d');
                            var config2 = {
                                type: 'bar',
                                data: {
                                    labels: chart_x,
                                    tooltipText: description_x,
                                    datasets: [{
                                        label:  splitNutrient + ' per 100g of Food',
                                        data: chart_y,
                                        // bootstrap colors
                                        // https://i.pinimg.com/originals/b8/70/f6/b870f6c3cf2f275906616de26cffaa52.png
                                        backgroundColor: 'rgba(76,175,80,0.7)',
                                        //hoverBackgroundColor: 'rgba(255,152,0,0.7)'
                                    }]
                                },
                                options: {
                                    scales: {
                                        yAxes: [{
                                            ticks: {
                                                //min: 0, //actual 0
                                                //max: 800 //actual is 707
                                                callback: function(value, index, values){
                                                    return value + splitUnit ;
                                                }
                                            }
                                        }]
                                    },
                                    responsive: true,
                                    tooltips: {
                                        callbacks: {
                                            title: function(tooltipItem, data) {
                                                var title = data.tooltipText[tooltipItem[0].index];
                                                return title;
                                            },
                                            afterLabel: function(tooltipItem, data) {
                                                var top = "CO2 per 100g: " + top_emission[tooltipItem.index];
                                                return top;
                                            }
                                        }
                                    }

                                }
                            };
                            Chart.defaults.global.defaultFontSize = 14;
                            myChart2 = new Chart(ctx2, config2);
                            chartExist2 = true;
                            // reset data
                            console.log(chart_x);
                            //showTable(food_data);
                            food_data = original_data;
                        }

                    </script> -->


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