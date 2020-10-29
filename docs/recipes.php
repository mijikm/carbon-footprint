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

$calorieData = "SELECT * FROM combined_data WHERE nutrient='Energy_kcal' ORDER BY food_name ASC";
$calorieQuery = mysqli_query($db,$calorieData);
$calorieArray = array();
foreach ($calorieQuery as $row) {
    $calorieArray[] = $row;
}

$nutrientData = "SELECT * FROM combined_data WHERE nutrient!='Energy_kcal' and nutrient!= 'sum_emission' ORDER BY food_name ASC";
$nutrientQuery = mysqli_query($db,$nutrientData);
$nutrientArray = array();
foreach ($nutrientQuery as $row) {
    $nutrientArray[] = $row;
}

$recommendData = "SELECT * FROM nutrient_recommender WHERE type='Normal' ORDER BY gender ASC";
$recommendQuery = mysqli_query($db,$recommendData);
$recommendArray = array();
foreach ($recommendQuery as $row) {
    $recommendArray[] = $row;
}

?>

<!--
	Ion by TEMPLATED
	templated.co @templatedco
	Released for free under the Creative Commons Attribution 3.0 license (templated.co/license)
-->
<!DOCTYPE HTML>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <title>Recipes - Trailblazers</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
    <!-- jQuery library -->
    <script src="js/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <script src="js/Chart.min.js"></script>
    <!-- autosave plugin -->
    <script src="js/savy.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <script type="text/javascript">
        var globalValue = 50;
        // searchFunction() is called when type in Search box
        function searchFunction() {
            var match, input, filter, ul, li, a, i;
            match = false;
            input = document.getElementById('myinput');
            filter = input.value.toUpperCase();
            ul = document.getElementById('recipe_wrapper');
            li = ul.getElementsByTagName('li');

            for(i=0; i<li.length;i++){
                a= li[i].getElementsByTagName('a')[0];
                if(a.innerHTML.toUpperCase().indexOf(filter) > -1) {
                    match = true;
                    li[i].style.display="";
                }
                else{
                    li[i].style.display='none';
                }
            }

            /* show Recipe not available conditionally */
            if (match === false) {
                document.querySelector('.not-found').style.display='block';
            } else {
                document.querySelector('.not-found').style.display='none';
            }
            globalValue = $('.recipe_main_container ul li:visible').length;
            /*
            $(function(){
                recipe_visible_function = function(){}
                if(globalValue < 1) {
                    document.querySelector('.not-found').style.display='block';
                } else {
                    document.querySelector('.not-found').style.display='none';
                }
            });*/
            console.log("searchfunction: " + globalValue);

        }

        $(document).ready(function(){


            // scroll to top
            const toTop = document.querySelector(".to-top");
            window.addEventListener("scroll",() => {
                if(window.pageYOffset > 100) {
                    toTop.classList.add("active");
                } else {
                    toTop.classList.remove("active");
                }
            })

            //filtering button for ALL, BREAFKAST, LUNCH, DINNER
            $('.recipe_type').click(function(){
                searchFunction();
                const value = $(this).attr('data-filter');
                if (value == 'all') {
                    $('.recipe_gallery').show('1000');
                } else {
                    $('.recipe_gallery').not('.'+value).hide('1000');
                    $('.recipe_gallery').filter('.'+value).show('1000');
                }

                if($('.recipe_wrapper li:visible').length < 1) {
                    document.querySelector('.not-found').style.display='block';
                } else {
                    document.querySelector('.not-found').style.display='none';
                }

                console.log("recipe_type: " + $('.recipe_main_container ul li:visible').length);
            })

            //add active class on selected item
            $('.recipe_type').click(function(){
                $(this).addClass('active').siblings().removeClass('active')
            })
            var isSorted = false;
            // sort by tree
            $('#sort-tree').click(function(){
                if (isSorted == false) {
                    $('.recipe_wrapper div').sort(function(a,b){
                        return $(a).data('worth') - $(b).data('worth');
                    }).appendTo('.recipe_wrapper')
                    isSorted = true;
                } else {
                    $('.recipe_wrapper div').sort(function(a,b){
                        return $(b).data('worth') - $(a).data('worth');
                    }).appendTo('.recipe_wrapper')
                    isSorted = false;
                }
            });
        })

        $(document).ready(function(){
            var count = 0;
            var bmr;
            var nutriArray;
            //var isValid = false;
            var recipeDict = {};
            var recipeEnergy = 0;
            var recipeCarbs = 0;
            var recipeFat = 0;
            var recipeProtein = 0;
            var pbEnergy = 0;
            var pbCarbs = 0;
            var pbFat = 0;
            var pbProtein = 0;

            // initialise pb first
            class ProgressBar {
                constructor(element, initialValue=0) {
                    this.valueElem = element.querySelector('.progress-bar-value');
                    this.fillElem = element.querySelector('.progress-bar-fill');

                    this.setValue(initialValue);
                }
                setValue (newValue) {
                    if (newValue < 0) {
                        newValue = 0;
                    }

                    this.value=newValue;
                    this.update();
                }
                update () {
                    const percentage = this.value + "%";
                    if (this.value > 100) {
                        this.fillElem.style.width = "100%";
                        this.valueElem.textContent = percentage;
                    } else {
                        this.fillElem.style.width = percentage;
                        this.valueElem.textContent = percentage;
                    }

                }
            }

            const pb1 = new ProgressBar(document.querySelector('.progress-bar-energy'), 0);
            const pb2 = new ProgressBar(document.querySelector('.progress-bar-carb'), 0);
            const pb3 = new ProgressBar(document.querySelector('.progress-bar-fat'), 0);
            const pb4 = new ProgressBar(document.querySelector('.progress-bar-protein'), 0);

            //use localStorage - openDiv(), save(), load() - for sidebar profile
            function openDiv() {
                var profile = document.getElementById("total_result2");
                var stepOne = document.getElementById("bmr_calculator_form")
                if(profile.style.display === "none"){
                    stepOne.style.display = "none";
                    profile.style.display = "block";
                    document.getElementById("result_bmr").innerHTML = localStorage.getItem('resultBmr');
                    document.getElementById("bar_bmr").innerHTML = localStorage.getItem('barBmr');
                    document.getElementById("bar_carb_bmr").innerHTML = localStorage.getItem('barCarbBmr');
                    document.getElementById("bar_fat_bmr").innerHTML = localStorage.getItem('barFatBmr');
                    document.getElementById("bar_protein_bmr").innerHTML = localStorage.getItem('barProteinBmr');
                    document.getElementById("bar_calories").innerHTML = localStorage.getItem('barCalories');
                    document.getElementById("bar_carb").innerHTML = localStorage.getItem('barCarb');
                    document.getElementById("bar_fat").innerHTML = localStorage.getItem('barFat');
                    document.getElementById("bar_protein").innerHTML = localStorage.getItem('barProtein');
                    pb1.setValue(parseFloat(localStorage.getItem('pbEnergy')));
                    pb2.setValue(parseFloat(localStorage.getItem('pbCarbs')));
                    pb3.setValue(parseFloat(localStorage.getItem('pbFat')));
                    pb4.setValue(parseFloat(localStorage.getItem('pbProtein')));
                    pbEnergy = parseFloat(localStorage.getItem('pbEnergy'));
                    pbCarbs = parseFloat(localStorage.getItem('pbCarbs'));
                    pbFat = parseFloat(localStorage.getItem('pbFat'));
                    pbProtein = parseFloat(localStorage.getItem('pbProtein'));
                    recipeEnergy = parseFloat(localStorage.getItem('recipeEnergy'));
                    recipeCarbs = parseFloat(localStorage.getItem('recipeCarbs'));
                    recipeFat = parseFloat(localStorage.getItem('recipeFat'));
                    recipeProtein = parseFloat(localStorage.getItem('recipeProtein'));
                    bmr = localStorage.getItem('bmr');
                    nutriArray = JSON.parse(localStorage.getItem('nutriArray'));
                    recipeDict = JSON.parse(localStorage.getItem('recipeDict'));
                    $('#recipe_list').append(localStorage.getItem('recipeList'));
                }
            }

            function save() {
                openDiv();
                var saveDiv = document.getElementById("total_result2");
                if (saveDiv.style.display === "block") {
                    localStorage.setItem("isVisible", true);
                    localStorage.resultBmr = document.getElementById("result_bmr").innerHTML; //Male 22yrs 80kg Light Activity
                    localStorage.barBmr = document.getElementById("bar_bmr").innerHTML; // user required calories (2502.59 kcal)
                    localStorage.barCarbBmr = document.getElementById("bar_carb_bmr").innerHTML; // user required carb (450.45 kcal)
                    localStorage.barFatBmr = document.getElementById("bar_fat_bmr").innerHTML; // user required fat (20.8 kcal)
                    localStorage.barProteinBmr = document.getElementById("bar_protein_bmr").innerHTML; // user required protein (83.2 kcal)
                    localStorage.barCalories = document.getElementById("bar_calories").innerHTML; //0 kcal
                    localStorage.barCarb = document.getElementById("bar_carb").innerHTML; // 0g
                    localStorage.barFat = document.getElementById("bar_fat").innerHTML; // 0g
                    localStorage.barProtein = document.getElementById("bar_protein").innerHTML; // 0g
                    localStorage.setItem("pbEnergy", pbEnergy);
                    localStorage.setItem("pbCarbs", pbCarbs);
                    localStorage.setItem("pbFat", pbFat);
                    localStorage.setItem("pbProtein", pbProtein);
                    pb1.setValue(parseFloat(localStorage.getItem('pbEnergy')));
                    pb2.setValue(parseFloat(localStorage.getItem('pbCarbs')));
                    pb3.setValue(parseFloat(localStorage.getItem('pbFat')));
                    pb4.setValue(parseFloat(localStorage.getItem('pbProtein')));
                    localStorage.setItem("bmr", bmr); // 2502.50
                    localStorage.setItem("recipeEnergy", recipeEnergy);
                    localStorage.setItem("recipeCarbs", recipeCarbs);
                    localStorage.setItem("recipeFat", recipeFat);
                    localStorage.setItem("recipeProtein", recipeProtein);
                    localStorage.setItem("nutriArray", JSON.stringify(nutriArray)); // [required fat, required protein, x, x, x, x, required carb]
                    localStorage.setItem("recipeDict",JSON.stringify(recipeDict)); //save it to recipeDict where all the recipe names exist; {"Vegan":["4","4","18","10"]}
                }
            }

            function load() {
                var isVisible = localStorage.getItem("isVisible");
                if (isVisible == "true") { //if user has already saved the profile
                    openDiv();
                }
            }

            load();

            $(function(){
                $('.auto_save').savy('load');
            });

            $('[data-toggle="tooltip"]').tooltip();
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const navBar = document.getElementById('navBar');
            hamburgerBtn.addEventListener('click', () => {
                navBar.classList.toggle('open');
            });

            $('#bmr_calculator_form').on('submit',function(event){
                event.preventDefault();
            });

            // SAVE PROFILE button
            $(function(){
                $("#calories_button").click(function(){
                    if($('#bmr_calculator_form')[0].checkValidity() === true){
                        bmr = calculate_calories();
                        nutriArray = calculate_nutrient(bmr);
                        if(localStorage.barCalories === undefined) { //if nothing saved
                            $('#bar_calories').html(0+"&nbsp;kcal");
                            $('#bar_carb').html(0+"&nbsp;g");
                            $('#bar_fat').html(0+"&nbsp;g");
                            $('#bar_protein').html(0+"&nbsp;g");
                        } else { //recalculate progress bar + percentage
                            /* NEW */
                            pbEnergy = Math.floor(recipeEnergy/bmr*100);
                            pbCarbs = Math.floor(recipeCarbs/nutriArray[6]*100);
                            pbFat = Math.floor(recipeFat/nutriArray[0]*100);
                            pbProtein = Math.floor(recipeProtein/nutriArray[1]*100);
                            /*END */
                        }
                        save();
                    }
                });
            });

            $(function(){
                $("#return_button").click(function(){
                    $('#total_result2').css("display","none");
                    $('#bmr_calculator_form').css("display","block");
                });
            });

            $("#recipe_list").on("click", '.delete', function() {
                var temp = $(this).closest("div[data-id]").attr('data-id');
                var requiredCarb = localStorage.getItem('barCarbBmr').match(/[0-9.]+/g);
                var requiredFat = localStorage.getItem('barFatBmr').match(/[0-9.]+/g);
                var requiredProtein = localStorage.getItem('barProteinBmr').match(/[0-9.]+/g);
                var tempP1 = Number(recipeDict[temp][0]/bmr*100).toFixed(0); //energy %
                var tempP2 = Number(recipeDict[temp][1]/requiredCarb*100).toFixed(0); //carbs %
                var tempP3 = Number(recipeDict[temp][2]/requiredFat*100).toFixed(0); //fat %
                var tempP4 = Number(recipeDict[temp][3]/requiredProtein*100).toFixed(0); //protein %
                var itemCal = recipeDict[temp][0];
                var itemCarb = recipeDict[temp][1];
                var itemFat= recipeDict[temp][2];
                var itemProtein= recipeDict[temp][3];

                pbEnergy -= parseFloat(tempP1);
                pb1.setValue(tempP1);
                pbCarbs -= parseFloat(tempP2);
                pb2.setValue(tempP2);
                pbFat -= parseFloat(tempP3);
                pb3.setValue(tempP3);
                pbProtein -= parseFloat(tempP4);
                pb4.setValue(tempP4);

                recipeEnergy -= itemCal
                recipeEnergy = Number(recipeEnergy).toFixed(2);
                recipeCarbs -= itemCarb;
                recipeCarbs = Number(recipeCarbs).toFixed(2);
                recipeFat -= itemFat;
                recipeFat = Number(recipeFat).toFixed(2);
                recipeProtein -= itemProtein;
                recipeProtein = Number(recipeProtein).toFixed(2);

                $('#bar_calories').html(recipeEnergy + "&nbsp;kcal");
                $('#bar_carb').html(recipeCarbs + "&nbsp;g");
                $('#bar_fat').html(recipeFat + "&nbsp;g");
                $('#bar_protein').html(recipeProtein + "&nbsp;g");

                delete recipeDict[temp];

                $(this).closest('.recipeDiv').remove();
                var updateValue = document.getElementById('recipe_list').innerHTML;
                localStorage.setItem("recipeList",updateValue); //update the recipe list div
                save();
            });

        });
    </script>
    <script type="text/javascript">
        var male = "";
        var female = "";
        var appear = false;
        var valid = false;
        var valid2 = false;
        var isValid = true;
        var isValid2 = true;
        var rowExists = false;
        var calorieData =  <?php echo json_encode($calorieArray);?>;
        var nutrientData =  <?php echo json_encode($nutrientArray);?>;
        var recommendData = <?php echo json_encode($recommendArray);?>;
        var selectedRow = null;
        var imgExists = false;
        var imgName = "";

        function calculate_calories() {
            var gender = "";
            var height = parseFloat(document.getElementById("height").value);
            var weight = parseFloat(document.getElementById("weight").value);
            var age = parseInt(document.getElementById("age").value);
            var activity = "";
            var bmr = "";

            activity = checkActivity();
            if (document.getElementById('male').checked) {
                gender = "Male";
                bmr = (10 * weight) + (6.25 * height) - (5 * age) + 5;
                bmr = bmr * activity;
                bmr = Number(bmr).toFixed(2);
            } else if (document.getElementById('female').checked) {
                gender = "Female";
                bmr = (10 * weight) + (6.25 * height) - (5 * age) - 161;
                bmr = bmr * activity;
                bmr = Number(bmr).toFixed(2);
            }
            document.getElementById("bmr_calculator_form").style.display = "none";
            document.getElementById("total_result2").style.display = "block";
            activity = document.getElementById("activity").value;
            document.getElementById("result_bmr").innerHTML = gender+" · "+age+"yrs"+" · "+ weight+"kg"+" · "+activity+"&nbsp;Activity";
            document.getElementById("bar_bmr").innerHTML = bmr + "&nbsp;kcal";
            return bmr;
        }

        function calculate_nutrient(bmr_value) {
            var tempArray;
            var maleArray;
            var femaleArray;
            var userAge;
            var nutriArray; // nutrient
            var bmr = bmr_value;
            if (document.getElementById('male').checked) {
                tempArray = recommendData.filter(function (x) {
                    return x.gender == "male";
                });
                userAge = document.getElementById('age').value;
                maleArray = checkAge(userAge,tempArray);
                nutriArray = checkNutrient(maleArray, bmr);
            } else if (document.getElementById('female').checked) {
                tempArray = recommendData.filter(function (x) {
                    return x.gender == "female";
                });
                userAge = parseInt(document.getElementById('age'));
                femaleArray = checkAge(userAge,tempArray);
                nutriArray = checkNutrient(femaleArray, bmr);
            }

            document.getElementById("result_nutrient").innerHTML = "Carbohydrates " + nutriArray[6] +"g, " +
                "Fats " + nutriArray[0] + "g, " + "Proteins " + nutriArray[1] + "g, " + "Vitamin A " + nutriArray[2] +"mcg, " +
                "Vitamin C " + nutriArray[3] +"mg, " + "Vitamin E " + nutriArray[4] + "mg, " +
                "Calcium " + nutriArray[5] +"mg";
            document.getElementById("bar_carb_bmr").innerHTML = nutriArray[6] + "&nbsp;g";
            document.getElementById("bar_fat_bmr").innerHTML = nutriArray[0] + "&nbsp;g";
            document.getElementById("bar_protein_bmr").innerHTML = nutriArray[1] + "&nbsp;g";
            return nutriArray;
        }

        function checkAge(userAge, userArray){
            var genderArray = userArray;

            if(userAge>0 && userAge <4) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "1-3";
                });
            } else if (userAge>3 && userAge <9) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "4-8";
                });
            } else if (userAge>8 && userAge <14) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "9-13";
                });
            } else if (userAge>13 && userAge <19) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "14-18";
                });
            } else if (userAge>18 && userAge <31) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "19-30";
                });
            } else if (userAge>30 && userAge <51) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "31-50";
                });
            } else if (userAge>50 && userAge <71) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == "51-70";
                });
            } else if (userAge > 70) {
                genderArray = genderArray.filter(function (x) {
                    return x.age_range == ">71";
                });
            }
            return genderArray;
        }

        function checkNutrient(userArray, bmr_value){
            var nutriArray = []; // Fats, Proteins, Vitamin A, Vitamin C, Vitamin E, Calcium, Carbohydrates,
            var nutriCarbo;
            var nutriFat;
            var nutriPro;
            var nutriVA;
            var nutriVC;
            var nutriVE;
            var nutriCal;
            var bmr = bmr_value;
            for (var key in userArray){
                if (userArray[key].nutrient_type == "Fat(g)"){
                    nutriFat = userArray[key].value;
                } else if (userArray[key].nutrient_type == "Protein(g)"){
                    nutriPro = userArray[key].value;
                } else if (userArray[key].nutrient_type == "Vit_A(mcg)"){
                    nutriVA = userArray[key].value;
                } else if (userArray[key].nutrient_type == "Vit_C(mg)"){
                    nutriVC = userArray[key].value;
                } else if (userArray[key].nutrient_type == "Vit_E(mg)"){
                    nutriVE = userArray[key].value;
                } else if (userArray[key].nutrient_type == "Calcium(mg)"){
                    nutriCal = userArray[key].value;
                }
            }
            nutriArray.push(nutriFat,nutriPro, nutriVA, nutriVC, nutriVE, nutriCal);

            //check activity and multiply value
            var nutriFactor;
            if (document.getElementById("activity").value=="Sedentary"){
                nutriFactor = 1.4;
            } else if (document.getElementById("activity").value=="Light"){
                nutriFactor = 1.6;
            } else if (document.getElementById("activity").value=="Moderate") {
                nutriFactor = 1.8;
            } else if (document.getElementById("activity").value=="Very Active") {
                nutriFactor = 2;
            } else { //extra active
                nutriFactor = 2.2;
            }
            // range of carbs 45% ~ 65%
            nutriCarbo = (Number(nutriFactor*bmr*0.45/4).toFixed(2)); //https://healthyeating.sfgate.com/recommended-amount-percent-carbohydrates-per-day-7287.html

            for (var i=0; i<nutriArray.length; i++) {
                nutriArray[i] *= nutriFactor; // multiply value times 2
                nutriArray[i] = Number(nutriArray[i].toFixed(2));
            }
            // the last item of nutriArray is the range of carbs
            nutriArray.push(nutriCarbo);
            return nutriArray;
        }

        function checkActivity(){
            if (document.getElementById("activity").value=="Sedentary"){
                return 1.2;
            } else if (document.getElementById("activity").value=="Light"){
                return 1.375;
            } else if (document.getElementById("activity").value=="Moderate") {
                return 1.55;
            } else if (document.getElementById("activity").value=="Very Active") {
                return 1.725;
            } else { //extra active
                return 1.9;
            }
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
    <link rel="stylesheet" href="css/font-awesome.min.css">

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
</head>
<body id="top" class="meal_body">
<!-- Header -->
<header id="header" class="skel-layers-fixed">
    <h5 class="team_logo"><a href="index.html">Trailblazers</a></h5>
    <nav id="nav">
        <ul>
            <li><a href="index.html">Home</a></li>
            <li><a href="carbon_footprint.php">What's Your Footprint?</a></li>
            <li><a href="meal_plan.php">Meal Planning</a></li>
            <li><a href="recipes.php" class="active-page">Recipes</a></li>
            <li><a href="facts.html" >Facts</a></li>
        </ul>
    </nav>
</header>
<div class="breadcrumb container">
    <a href="index.html">Home</a>&nbsp; >&nbsp;
    <span>Recipes</span>
</div>
<!--scroll to top-->
<a href="#" class="to-top">
    <button id="to-top" class="btn"><span class="glyphicon glyphicon-chevron-up"></span></button>
</a>
<!-- Banner -->
<div class="container">
    <div id="hamburgerBox"></div>
    <div id="hamburgerBtn">&#9776 </div>
</div>
<div class="container">
    <div id="recipeBanner">
        <br><br><br><br>
        <header class="major">
            <h3 style="color:#ffffff; font-weight: bold;">Recipes</h3>
            <p style="color: #ffffff">Eat healthy with low carbon footprint vegetarian meals</p>
        </header>
    </div>
</div>
<br>
<div class="container">
    <nav id="navBar">
        <div class="nav-brand">
            <form method="post" id="bmr_calculator_form" style="display:block;">
                <p style="font-weight: 500;">STEP 1.<br>Check your daily energy requirements</p>
                <div id="form-group2" class="form-group2">
                    <p class="bmr_form">Gender</p><br>
                    <div class="first_label" style="display: inline-block;">
                        <input class="first_label auto_save" type="radio" id="male" name="gender" checked/>
                        <label for="male" style="color:#000000;">Male</label>
                        <input class="auto_save" type="radio" id="female" name="gender" />
                        <label for="female" style="color:#000000;">Female</label>
                    </div><br>
                    <p class="bmr_form">Height</p>
                    <input class="input_height auto_save" id="height" name="height" type="number" min="1" step="0.01" placeholder="cm" required><br>

                    <p class="bmr_form">Weight</p>
                    <input class="input_weight auto_save" id="weight" name="weight" type="number" min="1" step="0.01" placeholder="kg" required>
                    <br>

                    <p class="bmr_form">Age</p>
                    <input class="input_age auto_save" id="age" name="age"  type="number" min="1" step="1" placeholder="Enter age" required>
                    <br>

                    <p class="bmr_form">Activity</p>
                    <select class="input_activity auto_save" name="activity" id="activity" required>
                        <option class="input_activity auto_save" value="">Select activity level</option>
                        <option class="input_activity auto_save" value="Sedentary">Sedentary: little to no exercise</option>
                        <option class="input_activity auto_save" value="Light">Light: exercise 1-3 times per week</option>
                        <option class="input_activity auto_save" value="Moderate">Moderate: exercise 4-5 times per week</option>
                        <option class="input_activity auto_save" value="Very Active">Very active: intense exercise 6-7 times per week </option>
                        <option class="input_activity auto_save" value="Extra Active">Extra active: very intense exercise daily</option>
                    </select>&nbsp;<span class='glyphicon glyphicon-info-sign my-tooltip'
                                         title="Exercise: 15-30 mins of elevated heart rate activity&#013;Intense: 45-120 mins of elevated heart rate activity&#013;Very intense: 2+ hrs of elevated heart rate activity"></span>
                    <!--class="btn btn-primary"-->
                    <br><br><br>
                    <input type="submit" name="submit" class="profile" id="calories_button" value="SAVE PROFILE" />
                    <hr class="major" />
                </div> <!--div form-group-->
            </form>
        </div>
        <div class="result2" id="total_result2" style="display:none;">
            <p style="display: inline-block; margin-bottom:3px;color: black;">YOUR PROFILE</p><p id="result_bmr" style="display: inline-block; margin-bottom:5px;"></p>
            <ul class="actions">
                <li><a id="return_button" class="button profile">EDIT PROFILE</a></li>
            </ul>

            <p id="result_nutrient" style="display: none"></p>

            <div>
                <p style="display: inline-block; margin-bottom: 3px;color: black;">DAILY RECOMMENDATIONS</p>
            </div>
            <div>
                <p style="display:inline-block; margin: 0;">Energy&nbsp;</p><br>
                <p id="bar_calories" style="display: inline-block; margin: 0;"></p><p style="display: inline-block; margin: 0;">&nbsp;/&nbsp;</p><p id="bar_bmr" style="display: inline-block; margin: 0;"></p>
            </div>
            <div class="progress-bar-energy">
                <div class="progress-bar-value"></div>
                <div class="progress-bar-fill"></div>
            </div>

            <div>
                <p style="display: inline-block; margin: 0;">Carbs&nbsp;</p><span class='glyphicon glyphicon-info-sign my-tooltip' title="Carbohydrates"></span><br>
                <p id="bar_carb" style="display: inline-block; margin: 0;"></p><p style="display: inline-block; margin: 0;">&nbsp;/&nbsp;</p><p id="bar_carb_bmr" style="display: inline-block; margin: 0;"></p>
            </div>
            <div class="progress-bar-carb">
                <div class="progress-bar-value"></div>
                <div class="progress-bar-fill"></div>
            </div>

            <div>
                <p style="display: inline-block; margin: 0;">Fat&nbsp;</p><br> <!--pb3-->
                <p id="bar_fat" style="display: inline-block; margin: 0;"></p><p style="display: inline-block; margin: 0;">&nbsp;/&nbsp;</p><p id="bar_fat_bmr" style="display: inline-block; margin: 0;"></p>
            </div>
            <div class="progress-bar-fat">
                <div class="progress-bar-value"></div>
                <div class="progress-bar-fill"></div>
            </div>

            <div>
                <p style="display: inline-block; margin: 0;">Protein&nbsp;</p><br> <!--pb4-->
                <p id="bar_protein" style="display: inline-block; margin: 0;"></p><p style="display: inline-block; margin: 0;">&nbsp;/&nbsp;</p><p id="bar_protein_bmr" style="display: inline-block; margin: 0;"></p>
            </div>
            <div class="progress-bar-protein">
                <div class="progress-bar-value"></div>
                <div class="progress-bar-fill"></div>
            </div>
            <hr style="margin-top: 1em;" class="minor" />
            <p style="display: inline-block;font-weight: 500; margin-bottom:3px;color: #000000;">YOUR LIST</p>
            <div id="recipe_list">
            </div>
        </div>
    </nav>
</div>
<div class="container align-center">
    <div class="search-bar">
        <input type="text" name="search" value="" autocomplete="off" id="myinput" onkeyup="searchFunction()" placeholder="Search recipe">
    </div>
</div>
<div class="container align-center" style="margin-top: 60px;">
    <ul id="recipe_type_ul" style="margin-bottom: 5px;">
        <li class="recipe_type active" data-filter="all">ALL</li>
        <li class="recipe_type" data-filter="breakfast">BREAKFAST</li>
        <li class="recipe_type" data-filter="lunch">LUNCH</li>
        <li class="recipe_type" data-filter="dinner">DINNER</li>
    </ul>
    <button class="button small" style="background: #FFAF11;color: #000000;" id="sort-tree">Sort by Carbon Footprint <img src="images/tree_icon.png" height="20"/></button>
    <div class="not-found" style="display: none;">Recipe not available</div>
</div>

<div class="container align-center recipe_main_container">
    <ul id="recipe_wrapper" class="align-center recipe_wrapper" style="vertical-align: top;text-align: left">
        <a href="Vegan-Pistachio-And-Orange-Baklava.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="vegan_pistacio" src="images/recipe/Vegan Pistachio And Orange Baklava.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Vegan-Pistachio-And-Orange-Baklava.php" class="recipe_a">Vegan Pistachio And Orange Baklava</a>
                </li>
            </div>
        </a>
        <a href="The-Crispiest-Vegan-Fish-And-Chips.php">
            <div class="recipe_gallery lunch" data-worth="1.5">
                <li class="recipe_li">
                    <img id="the_crispiest" src="images/recipe/The Crispiest Vegan Fish And Chips.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="The-Crispiest-Vegan-Fish-And-Chips.php" class="recipe_a">The Crispiest Vegan Fish And Chips</a>
                </li>
            </div>
        </a>
        <a href="Broad-Bean-And-Basil-Risotto.php">
            <div class="recipe_gallery lunch dinner" data-worth="3">
                <li class="recipe_li">
                    <img id="broad_bean" src="images/recipe/Broad Bean And Basil Risotto.jpg" class="image recipe_img" >
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Broad-Bean-And-Basil-Risotto.php" class="recipe_a">Broad Bean And Basil Risotto</a>
                </li>
            </div>
        </a>
        <a href="Spicy-Courgette-Fritters.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="0.5">
                <li class="recipe_li" >
                    <img id="spicy_courgette" src="images/recipe/Spicy Courgette Fritters.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Spicy-Courgette-Fritters.php" class="recipe_a">Spicy Courgette Fritters</a>
                </li>
            </div>
        </a>
        <a href="Creamed-Aubergine-Wheat-With-Fried-Sugar-Snap-Peas.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="creamed_aubergine" src="images/recipe/Creamed Aubergine Wheat With Fried Sugar Snap Peas.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Creamed-Aubergine-Wheat-With-Fried-Sugar-Snap-Peas.php" class="recipe_a">Creamed Aubergine Wheat With Fried Sugar Snap Peas</a>
                </li>
            </div>
        </a>
        <a href="Roasted-Veg-And-Chickpeas-With-A-Parsley-Crumb.php">
            <div class="recipe_gallery dinner" data-worth="3">
                <li class="recipe_li" >
                    <img id="roasted_veg" src="images/recipe/Roasted Veg And Chickpeas With A Parsley Crumb.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Roasted-Veg-And-Chickpeas-With-A-Parsley-Crumb.php" class="recipe_a">Roasted Veg And Chickpeas With A Parsley Crumb</a>
                </li>
            </div>
        </a>
        <a href="Herby-Pea-Pilaf.php">
            <div class="recipe_gallery lunch dinner" data-worth="1.5">
                <li class="recipe_li">
                    <img id="herby_pea" src="images/recipe/Herby Pea Pilaf.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Herby-Pea-Pilaf.php" class="recipe_a">Herby Pea Pilaf</a>
                </li>
            </div>
        </a>
        <a href="Onion-Bhajis-(Plain-Flour-Recipe).php">
            <div class="recipe_gallery breakfast" data-worth="1">
                <li class="recipe_li">
                    <img id="onion_bhajis" src="images/recipe/Onion Bhajis (Plain Flour Recipe).jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Onion-Bhajis-(Plain-Flour-Recipe).php" class="recipe_a">Onion Bhajis (Plain Flour Recipe)</a>
                </li>
            </div>
        </a>
        <a href="Spiced-Couscous-Salad-With-Crispy-Spring-Onions.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="spiced_couscous" src="images/recipe/Spiced Couscous Salad With Crispy Spring Onions.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Spiced-Couscous-Salad-With-Crispy-Spring-Onions.php" class="recipe_a">Spiced Couscous Salad With Crispy Spring Onions</a>
                </li>
            </div>
        </a>
        <a href="Smoked-Tofu-Kedgeree.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="1.5">
                <li class="recipe_li">
                    <img id="smoked_tofu" src="images/recipe/Smoked Tofu Kedgeree.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Smoked-Tofu-Kedgeree.php" class="recipe_a">Smoked Tofu Kedgeree</a>
                </li>
            </div>
        </a>
        <a href="Quick-Mushroom-And-Lentil-Ragu.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="quick_mushroom" src="images/recipe/Quick Mushroom And Lentil Ragu.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Quick-Mushroom-And-Lentil-Ragu.php" class="recipe_a">Quick Mushroom And Lentil Ragu</a>
                </li>
            </div>
        </a>
        <a href="Caribbean-Spiced-Spinach-Dhal.php">
            <div class="recipe_gallery dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="caribbean_spiced" src="images/recipe/Caribbean Spiced Spinach Dhal.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Caribbean-Spiced-Spinach-Dhal.php" class="recipe_a">Caribbean Spiced Spinach Dhal</a>
                </li>
            </div>
        </a>
        <a href="The-Best-Vegan-Kentucky-Fried-Cauliflower.php">
            <div class="recipe_gallery breakfast lunch" data-worth="3">
                <li class="recipe_li">
                    <img id="the_best_vegan" src="images/recipe/The Best Vegan Kentucky Fried Cauliflower.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="The-Best-Vegan-Kentucky-Fried-Cauliflower.php" class="recipe_a">The Best Vegan Kentucky Fried Cauliflower</a>
                </li>
            </div>
        </a>
        <a href="Apple-And-Rhubarb-Turnovers.php">
            <div class="recipe_gallery breakfast" data-worth="2">
                <li class="recipe_li">
                    <img id="apple_and" src="images/recipe/Apple And Rhubarb Turnovers.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Apple-And-Rhubarb-Turnovers.php" class="recipe_a">Apple And Rhubarb Turnovers</a>
                </li>
            </div>
        </a>
        <a href="Mustardy-Potato-Salad-With-Rocket-And-Radish.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="mustardy_potato" src="images/recipe/Mustardy Potato Salad With Rocket And Radish.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Mustardy-Potato-Salad-With-Rocket-And-Radish.php" class="recipe_a">Mustardy Potato Salad With Rocket And Radish</a>
                </li>
            </div>
        </a>
        <a href="The-Best-Red-Cabbage-Ragu.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="the_best_red" src="images/recipe/The Best Red Cabbage Ragu.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="The-Best-Red-Cabbage-Ragu.php" class="recipe_a">The Best Red Cabbage Ragu</a>
                </li>
            </div>
        </a>
        <a href="Scrambled-Tofu-And-Tempeh-Bacon-Breakfast-Muffin.php">
            <div class="recipe_gallery breakfast" data-worth="2">
                <li class="recipe_li">
                    <img id="scrambled_tofu" src="images/recipe/Scrambled Tofu And Tempeh Bacon Breakfast Muffin.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Scrambled-Tofu-And-Tempeh-Bacon-Breakfast-Muffin.php" class="recipe_a">Scrambled Tofu And Tempeh Bacon Breakfast Muffin</a>
                </li>
            </div>
        </a>
        <a href="20-Minute-Vegan-Banana-Cake.php">
            <div class="recipe_gallery breakfast" data-worth="1.5">
                <li class="recipe_li">
                    <img id="20_minute" src="images/recipe/20 Minute Vegan Banana Cake.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="20-Minute-Vegan-Banana-Cake.php" class="recipe_a">20 Minute Vegan Banana Cake</a>
                </li>
            </div>
        </a>
        <a href="One-Pot-Pasta-With-A-Chickpea-And-Tomato-Sauce.php">
            <div class="recipe_gallery breakfast lunch" data-worth="3">
                <li class="recipe_li">
                    <img id="one_pot" src="images/recipe/One Pot Pasta With A Chickpea And Tomato Sauce.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="One-Pot-Pasta-With-A-Chickpea-And-Tomato-Sauce.php" class="recipe_a">One Pot Pasta With A Chickpea And Tomato Sauce</a>
                </li>
            </div>
        </a>
        <a href="Quick-Mediterranean-Spiced-Rice.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="1.5">
                <li class="recipe_li">
                    <img id="quick_mediterranean" src="images/recipe/Quick Mediterranean Spiced Rice.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Quick-Mediterranean-Spiced-Rice.php" class="recipe_a">Quick Mediterranean Spiced Rice</a>
                </li>
            </div>
        </a>
        <a href="Simple-Vegan-Pesto.php">
            <div class="recipe_gallery breakfast" data-worth="1">
                <li class="recipe_li">
                    <img id="simple_vegan" src="images/recipe/Simple Vegan Pesto.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Simple-Vegan-Pesto.php" class="recipe_a">Simple Vegan Pesto</a>
                </li>
            </div>
        </a>
        <a href="Blood-Orange-And-Hemp-Seed-Quinoa-Tabbouleh.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="blood_orange" src="images/recipe/Blood Orange And Hemp Seed Quinoa Tabbouleh.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Blood-Orange-And-Hemp-Seed-Quinoa-Tabbouleh.php" class="recipe_a">Blood Orange And Hemp Seed Quinoa Tabbouleh</a>
                </li>
            </div>
        </a>
        <a href="Molasses-Baked-Beans.php">
            <div class="recipe_gallery breakfast" data-worth="1">
                <li class="recipe_li">
                    <img id="molasses" src="images/recipe/Molasses Baked Beans.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Molasses-Baked-Beans.php" class="recipe_a">Molasses Baked Beans</a>
                </li>
            </div>
        </a>
        <a href="Stovetop-Popcorn-With-A-Baked-Orange-Glaze.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="stovetop" src="images/recipe/Stovetop Popcorn With A Baked Orange Glaze.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Stovetop-Popcorn-With-A-Baked-Orange-Glaze.php" class="recipe_a">Stovetop Popcorn With A Baked Orange Glaze</a>
                </li>
            </div>
        </a>
        <a href="Spinach-And-Kale-Saag-With-Spiced-Roast-Potatoes-And-Cauliflower.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="spinach" src="images/recipe/Spinach And Kale Saag With Spiced Roast Potatoes And Cauliflower.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Spinach-And-Kale-Saag-With-Spiced-Roast-Potatoes-And-Cauliflower.php" class="recipe_a">Spinach And Kale Saag With Spiced Roast Potatoes And Cauliflower</a>
                </li>
            </div>
        </a>
        <a href="Vegan-Rice-Pudding-With-Caramelized-Blood-Oranges-And-Pistachios.php">
            <div class="recipe_gallery breakfast" data-worth="1">
                <li class="recipe_li">
                    <img id="vegan_rice" src="images/recipe/Vegan Rice Pudding With Caramelized Blood Oranges And Pistachios.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Vegan-Rice-Pudding-With-Caramelized-Blood-Oranges-And-Pistachios.php" class="recipe_a">Vegan Rice Pudding With Caramelized Blood Oranges And Pistachios</a>
                </li>
            </div>
        </a>
        <a href="Peanut-Glazed-Swede-With-Green-Cabbage-And-Chilli-Noodles.php">
            <div class="recipe_gallery lunch dinner" data-worth="0.5">
                <li class="recipe_li">
                    <img id="peanut" src="images/recipe/Peanut Glazed Swede With Green Cabbage And Chilli Noodles.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Peanut-Glazed-Swede-With-Green-Cabbage-And-Chilli-Noodles.php" class="recipe_a">Peanut Glazed Swede With Green Cabbage And Chilli Noodles</a>
                </li>
            </div>
        </a>
        <a href="Baked-Black-Kale-Falafels.php">
            <div class="recipe_gallery breakfast" data-worth="1">
                <li class="recipe_li">
                    <img id="baked_black_kale" src="images/recipe/Baked Black Kale Falafels.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Baked-Black-Kale-Falafels.php" class="recipe_a">Baked Black Kale Falafels</a>
                </li>
            </div>
        </a>
        <a href="Vegan-Banana-Pancakes-With-A-Smashed-Blueberry-Sauce.php">
            <div class="recipe_gallery breakfast lunch" data-worth="1">
                <li class="recipe_li">
                    <img id="vegan_banana" src="images/recipe/Vegan Banana Pancakes With A Smashed Blueberry Sauce.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Vegan-Banana-Pancakes-With-A-Smashed-Blueberry-Sauce.php" class="recipe_a">Vegan Banana Pancakes With A Smashed Blueberry Sauce</a>
                </li>
            </div>
        </a>
        <a href="White-Wine-And-Mushroom-Cashew-Rigatoni-With-Steamed-Spring-Greens.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="white_wine" src="images/recipe/White Wine And Mushroom Cashew Rigatoni With Steamed Spring Greens.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="White-Wine-And-Mushroom-Cashew-Rigatoni-With-Steamed-Spring-Greens.php" class="recipe_a">White Wine And Mushroom Cashew Rigatoni With Steamed Spring Greens</a>
                </li>
            </div>
        </a>
        <a href="Speedy-Vegan-Chocolate-Mug-Cake.php">
            <div class="recipe_gallery breakfast lunch" data-worth="4">
                <li class="recipe_li">
                    <img id="speedy_vegan" src="images/recipe/Speedy Vegan Chocolate Mug Cake.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Speedy-Vegan-Chocolate-Mug-Cake.php" class="recipe_a">Speedy Vegan Chocolate Mug Cake</a>
                </li>
            </div>
        </a>
        <a href="Sweet-Potato-Rosti-With-Smokey-Black-Beans-And-Blackened-Spring-Onion-Corn-And-Tomato-Salsa.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="sweet_potato" src="images/recipe/Sweet Potato Rosti With Smokey Black Beans And Blackened Spring Onion, Corn And Tomato Salsa.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Sweet-Potato-Rosti-With-Smokey-Black-Beans-And-Blackened-Spring-Onion-Corn-And-Tomato-Salsa.php" class="recipe_a">Sweet Potato Rosti With Smokey Black Beans And Blackened Spring Onion, Corn And Tomato Salsa</a>
                </li>
            </div>
        </a>
        <a href="Summer-Minestrone.php">
            <div class="recipe_gallery lunch dinner" data-worth="1.5">
                <li class="recipe_li">
                    <img id="summer" src="images/recipe/Summer Minestrone.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Summer-Minestrone.php" class="recipe_a">Summer Minestrone</a>
                </li>
            </div>
        </a>
        <a href="Roasted-Red-Pepper-And-Tomato-Bread-Soup.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="roasted_red" src="images/recipe/Roasted Red Pepper And Tomato Bread Soup.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Roasted-Red-Pepper-And-Tomato-Bread-Soup.php" class="recipe_a">Roasted Red Pepper And Tomato Bread Soup</a>
                </li>
            </div>
        </a>
        <a href="Buckwheat-And-Peach-Salad-With-French-Beans-And-Balsamic-Glaze.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="buckwheat" src="images/recipe/Buckwheat And Peach Salad With French Beans And Balsamic Glaze.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Buckwheat-And-Peach-Salad-With-French-Beans-And-Balsamic-Glaze.php" class="recipe_a">Buckwheat And Peach Salad With French Beans And Balsamic Glaze</a>
                </li>
            </div>
        </a>
        <a href="Braised-Courgettes-With-Haricot-Beans-And-Pistou.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="braised" src="images/recipe/Braised Courgettes With Haricot Beans And Pistou.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Braised-Courgettes-With-Haricot-Beans-And-Pistou.php" class="recipe_a">Braised Courgettes With Haricot Beans And Pistou</a>
                </li>
            </div>
        </a>
        <a href="Tomato-Galette-With-Garlic-Whipped-Tahini-And-Fried-Olives.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="tomato" src="images/recipe/Tomato Galette With Garlic Whipped Tahini And Fried Olives.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Tomato-Galette-With-Garlic-Whipped-Tahini-And-Fried-Olives.php" class="recipe_a">Tomato Galette With Garlic Whipped Tahini And Fried Olives</a>
                </li>
            </div>
        </a>
        <a href="Fresh-Tomato-And-White-Bean-Summer-Stew-With-Fried-Aubergine.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="fresh_tomato" src="images/recipe/Fresh Tomato And White Bean Summer Stew With Fried Aubergine.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Fresh-Tomato-And-White-Bean-Summer-Stew-With-Fried-Aubergine.php" class="recipe_a">Fresh Tomato And White Bean Summer Stew With Fried Aubergine</a>
                </li>
            </div>
        </a>
        <a href="Chillied-Rice-With-Zaatar-Roast-Chickpeas-And-Flat-Beans.php">
            <div class="recipe_gallery lunch dinner" data-worth="2.5">
                <li class="recipe_li">
                    <img id="chillied" src="images/recipe/Chillied Rice With Zaatar Roast Chickpeas And Flat Beans.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Chillied-Rice-With-Zaatar-Roast-Chickpeas-And-Flat-Beans.php" class="recipe_a">Chillied Rice With Zaatar Roast Chickpeas And Flat Beans</a>
                </li>
            </div>
        </a>
        <a href="Sticky-Chinese-Five-Spice-Vegetable-Stir-Fry.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="sticky" src="images/recipe/Sticky Chinese Five Spice Vegetable Stir Fry.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Sticky-Chinese-Five-Spice-Vegetable-Stir-Fry.php" class="recipe_a">Sticky Chinese Five Spice Vegetable Stir Fry</a>
                </li>
            </div>
        </a>
        <a href="Spelt-And-Courgette-Summer-Soup.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="spelt" src="images/recipe/Spelt And Courgette Summer Soup.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Spelt-And-Courgette-Summer-Soup.php" class="recipe_a">Spelt And Courgette Summer Soup</a>
                </li>
            </div>
        </a>
        <a href="Moroccan-Spiced-Tomato-Lentils-With-Grilled-Courgettes.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="moroccan" src="images/recipe/Moroccan Spiced Tomato Lentils With Grilled Courgettes.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Moroccan-Spiced-Tomato-Lentils-With-Grilled-Courgettes.php" class="recipe_a">Moroccan Spiced Tomato Lentils With Grilled Courgettes</a>
                </li>
            </div>
        </a>
        <a href="Spinach-And-Red-Pepper-Thai-Curry.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="spinach" src="images/recipe/Spinach And Red Pepper Thai Curry.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Spinach-And-Red-Pepper-Thai-Curry.php" class="recipe_a">Spinach And Red Pepper Thai Curry</a>
                </li>
            </div>
        </a>
        <a href="Balsamic-Roasted-Fennel-With-A-Bulgur-And-Herb-Salad.php">
            <div class="recipe_gallery lunch dinner" data-worth="3">
                <li class="recipe_li">
                    <img id="balsamic" src="images/recipe/Balsamic Roasted Fennel With A Bulgur And Herb Salad.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Balsamic-Roasted-Fennel-With-A-Bulgur-And-Herb-Salad.php" class="recipe_a">Balsamic Roasted Fennel With A Bulgur And Herb Salad</a>
                </li>
            </div>
        </a>
        <a href="Heritage-Tomato-And-Couscous-Salad-With-Toasted-Thyme-Breadcrumbs.php">
            <div class="recipe_gallery lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="heritage" src="images/recipe/Heritage Tomato And Couscous Salad With Toasted Thyme Breadcrumbs.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Heritage-Tomato-And-Couscous-Salad-With-Toasted-Thyme-Breadcrumbs.php" class="recipe_a">Heritage Tomato And Couscous Salad With Toasted Thyme Breadcrumbs</a>
                </li>
            </div>
        </a>
        <a href="Cashew-And-Wild-Garlic-Alfredo-With-Rigatoni.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="cashew" src="images/recipe/Cashew And Wild Garlic Alfredo With Rigatoni.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Cashew-And-Wild-Garlic-Alfredo-With-Rigatoni.php" class="recipe_a">Cashew And Wild Garlic Alfredo With Rigatoni</a>
                </li>
            </div>
        </a>
        <a href="Kale-And-Fresh-Mint-Soup.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="kale" src="images/recipe/Kale And Fresh Mint Soup.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Kale-And-Fresh-Mint-Soup.php" class="recipe_a">Kale And Fresh Mint Soup</a>
                </li>
            </div>
        </a>
        <a href="Spiced-Turnip-And-Chickpea-Couscous.php">
            <div class="recipe_gallery lunch dinner" data-worth="1">
                <li class="recipe_li">
                    <img id="spiced" src="images/recipe/Spiced Turnip And Chickpea Couscous.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Spiced-Turnip-And-Chickpea-Couscous.php" class="recipe_a">Spiced Turnip And Chickpea Couscous</a>
                </li>
            </div>
        </a>
        <a href="Gigli-With-Salted-Purple-Sprouting-Broccoli.php">
            <div class="recipe_gallery breakfast lunch dinner" data-worth="2">
                <li class="recipe_li">
                    <img id="gigli" src="images/recipe/Gigli With Salted Purple Sprouting Broccoli.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/tree_icon.png" height="20"/><img src="images/tree_icon.png" height="20"/><br>
                    <a href="Gigli-With-Salted-Purple-Sprouting-Broccoli.php" class="recipe_a">Gigli With Salted Purple Sprouting Broccoli</a>
                </li>
            </div>
        </a>
        <a href="Homemade-Gnocchi.php">
            <div class="recipe_gallery breakfast" data-worth="0.5">
                <li class="recipe_li">
                    <img id="homemade" src="images/recipe/Homemade Gnocchi.jpg" class="image recipe_img">
                    <p class="recipe_tree" style="display: inline-block;margin: 0;">Tree&nbsp;</p><img src="images/half_tree_icon.png" height="20"/><br>
                    <a href="Homemade-Gnocchi.php" class="recipe_a">Homemade Gnocchi</a>
                </li>
            </div>
        </a>
    </ul>
</div>

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