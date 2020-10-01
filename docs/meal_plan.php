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
$emissionData = "SELECT * FROM combined_data WHERE nutrient='sum_emission' ORDER BY food_name ASC";
$emissionQuery = mysqli_query($db,$emissionData);
$emissionArray = array();
foreach ($emissionQuery as $row) {
    $emissionArray[] = $row;
}

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

$sql="SELECT DISTINCT(food_group) FROM combined_data ORDER BY food_group ASC";
$result=mysqli_query($db,$sql);

function fill_select_box(){
    $db = db_connect();
    $sql="SELECT DISTINCT(food_group) FROM combined_data ORDER BY food_group ASC";
    $result=mysqli_query($db,$sql);
    $output='';
    while($row=mysqli_fetch_array($result)){
        $output .='<option value="'.$row["food_group"].'">'.$row["food_group"].'</option>';
    }
    echo $output;
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
    <title>Meal Planning - Trailblazers</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <!--[if lte IE 8]><script src="js/html5shiv.js"></script><![endif]-->
    <script src="js/jquery.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <script src="js/Chart.min.js"></script>

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- autosave plugin -->
    <script src="js/savy.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.3/js/bootstrapValidator.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $(function(){
                $('.auto_save').savy('load');
            });

            $('[data-toggle="tooltip"]').tooltip();
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const navBar = document.getElementById('navBar');
            hamburgerBtn.addEventListener('click', () => {
                console.log("Button clicked");
                navBar.classList.toggle('open');
            });

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
                    if (newValue > 100) {
                        newValue = 100;
                    }
                    this.value=newValue;
                    this.update();
                }
                update () {
                    const percentage = this.value + "%";
                    this.fillElem.style.width = percentage;
                    this.valueElem.textContent = percentage;
                }
            }

            const pb1 = new ProgressBar(document.querySelector('.progress-bar-energy'), 0);
            const pb2 = new ProgressBar(document.querySelector('.progress-bar-carb'), 0);
            const pb3 = new ProgressBar(document.querySelector('.progress-bar-fat'), 0);
            const pb4 = new ProgressBar(document.querySelector('.progress-bar-protein'), 0);

            var count = 0;
            var bmr;
            var nutriArray;

            $(document).on('click', '.add', function(){
                var tab_id = $('.tab-content .active').attr('id');
                if (tab_id==="breakfast_tab") {
                    tab_id = "first_table";
                } else if (tab_id ==="lunch_tab") {
                    tab_id = "lunch_table";
                } else if (tab_id === "dinner_tab") {
                    tab_id = "dinner_table"
                }
                count++;
                var html = '';
                html += '<tr>';
                html += '<td><select name="item_category[]" class="form-control item_category" data-sub_category_id="'+count+'" required><option value="" selected disabled>Select Food Type</option><?php echo fill_select_box(); ?></select></td>';
                html += '<td><select name="item_sub_category[]" class="form-control item_sub_category" data-sub_category_id="'+count+'" id="item_sub_category'+count+'" required><option value="" selected disabled>Select Food</option></select></td>';
                html += '<td><input name="item_weight" class="form-control item_weight" data-sub_category_id="'+count+'" placeholder="Enter" type="number" min="0.01" step="0.01" id="item_weight'+count+'" required></td>';
                html += '<td><select name="unit" id="unit'+count+'" class="form-control input_unit" data-sub_category_id="'+count+'" required><option value="" selected disabled>Select g/kg</option>\n' +
                    '                        <option value="g">g</option>\n' +
                    '                        <option value="kg">kg</option></select></td>';
                html += '<td><output class="item_emissions" id="item_emissions'+count+'"></output></td>'
                html += '<td><output class="item_calories" id="item_calories'+count+'"></output></td>'
                html += '<td><button type="button" name="remove" class="align-center btn btn-danger btn-xs remove"><span class="glyphicon glyphicon-minus"></span></button></td>';
                $('#'+tab_id).append(html);
                console.log(tab_id);
            });


            $(document).on('click','.remove', function(){
               $(this).closest('tr').remove();
                if ( $("#item_table tr").length < 2) {

                    $('#total_result').css("display","none");
                }
            });

            $(document).on('change','.item_category', function(){
                var food_group = $(this).val();
                var sub_category_id = $(this).data('sub_category_id');
                $.ajax({
                    url:"action_group.php",
                    //send data to server with POST method
                    method:"POST",
                    data:{food_group: food_group},
                    success:function(data){
                        var html = '<option value = "" selected disabled>Select Food</option>';
                        html += data;
                        $('#item_sub_category'+sub_category_id).html(html);
                        $('#item_weight'+sub_category_id).val("");
                        $('#unit'+sub_category_id)[0].selectedIndex = 0;
                        $('#item_emissions'+sub_category_id).html("");
                        $('#item_calories'+sub_category_id).html("");
                    }

                })
            });
            //validation
            $('#insert_form').on('submit',function(event){
                event.preventDefault();
                //var form_data = $(this).serialize();
                //$('#item_table').find("tr:gt(0)").remove();
            });

            $('#bmr_calculator_form').on('submit',function(event){
                event.preventDefault();
            });

            $(document).on('change', '.item_sub_category', function(){
                var food_name = $(this).val();
                var sub_category_id = $(this).data('sub_category_id');
                //if unit is chosen
                if (($('#unit'+sub_category_id).val() != "") && ($('#item_weight'+sub_category_id).val() != "")) {
                    var finals = [];
                    var unit = $('#unit'+sub_category_id).val();
                    var weight = $('#item_weight'+sub_category_id).val();
                    finals = addIngredient(food_name, unit, weight);
                    $('#item_emissions'+sub_category_id).html(finals[0]);
                    $('#item_calories'+sub_category_id).html(finals[1]);
                };
            });

            $(document).on('change', '.item_weight', function(){
                var weight = $(this).val();
                var sub_category_id = $(this).data('sub_category_id');
                var food_name = $('#item_sub_category'+sub_category_id).val();
                //if unit is chosen
                if (($('#unit'+sub_category_id).val() != "") && ($('#item_sub_category'+sub_category_id).val() != "")) {
                    var finals = [];
                    var unit = $('#unit'+sub_category_id).val();
                    finals = addIngredient(food_name, unit, weight);
                    $('#item_emissions'+sub_category_id).html(finals[0]);
                    $('#item_calories'+sub_category_id).html(finals[1]);
                };
            });

            $(document).on('change', '.input_unit', function(){
                var unit = $(this).val();
                var sub_category_id = $(this).data('sub_category_id');
                var food_name = $('#item_sub_category'+sub_category_id).val();
                //if weight is entered
                if ($('#item_weight'+sub_category_id).val() != "" && ($('#item_sub_category'+sub_category_id).val() != "")) {
                    var finals = [];
                    var weight = $('#item_weight'+sub_category_id).val();
                    finals = addIngredient(food_name, unit, weight);
                    $('#item_emissions'+sub_category_id).html(finals[0]);
                    $('#item_calories'+sub_category_id).html(finals[1]);
                };
            });

            $(function(){
                $("#calories_button").click(function(){
                    if($('#bmr_calculator_form')[0].checkValidity() === true){
                        bmr = calculate_calories();
                        nutriArray = calculate_nutrient(bmr);
                        $('#bar_calories').html(0+"&nbsp;kcal");
                        $('#bar_carb').html(0+"&nbsp;g");
                        $('#bar_fat').html(0+"&nbsp;g");
                        $('#bar_protein').html(0+"&nbsp;g");
                    }
                });
            });

            $(function(){
                $("#return_button").click(function(){
                    $('#total_result2').css("display","none");
                    $('#bmr_calculator_form').css("display","block");
                });
            });


            $(function(){
                $("#calculate_button").click(function(){
                    if($('#insert_form')[0].checkValidity() === true) {

                        $(':required:invalid', '#insert_form').each(function () {
                            var id = $('.tab-pane').find(':required:invalid').closest('.tab-pane').attr('id');

                            $('.nav a[href="#' + id + '"]').tab('show');
                        });

                        $('#total_result').css("display","block");
                        $('html,body').animate(
                            {
                                scrollTop:$('#total_result').offset().top
                            },
                            'slow'
                        )
                        //get the total gas emissions by each class
                        var sum = 0;
                        var cal = 0;

                        $('.item_emissions').each(function () {
                            var text = $(this).text().match(/[0-9.]+/g);  // extract float from string
                            sum += parseFloat(text);
                        });
                        $('#carbon_footprint').html(Number(sum/1000*365).toFixed(2) + " TONS");//annual
                        $('#tree_num').html(Number(sum/1000*365/0.07).toFixed(0)); //annual tree
                        $('#tree_num2').html(Number(sum/1000*365/0.07).toFixed(0));

                        $('.item_calories').each(function () {
                            var text = $(this).text().match(/[0-9.]+/g);  // extract float from string
                            cal += parseFloat(text);
                        });
                        $('#total_calories').html(Number(cal).toFixed(2) + " kcal");
                        $('#bar_calories').html(Number(cal).toFixed(2) + " kcal");
                        var percent = Number(Number(cal).toFixed(2) / bmr * 100).toFixed(0);
                        pb1.setValue(percent);

                        var nutrientDict = {};
                        //get the nutrient value
                        $('.item_weight').each(function () {
                            var sub_category_id = $(this).data('sub_category_id');
                            var weight = $(this).val();
                            var food_name = $('#item_sub_category' + sub_category_id).val();
                            var metric = $('#unit' + sub_category_id).val();
                            if (metric == "kg") {
                                weight *= 1000; // change from kg to g by multiplying 1000
                            }
                            // check dictionary if food exists
                            if (food_name in nutrientDict) {
                                var existingAmount = parseFloat(nutrientDict[food_name]);
                                weight = existingAmount + weight;
                                nutrientDict[food_name] = Number(weight).toFixed(2); // round up to two decimal places
                            } else {
                                nutrientDict[food_name] = Number(weight).toFixed(2);
                            }

                        });
                        var totalNutrient = checkNutrientData(nutrientDict);
                        $('#bar_carb').html(totalNutrient[0] + "&nbsp;g");
                        var percent = Number(totalNutrient[0] / (nutriArray[6]) * 100).toFixed(0);
                        pb2.setValue(percent);

                        $('#bar_fat').html(totalNutrient[1] + "&nbsp;g");
                        var percent = Number(totalNutrient[1] / (nutriArray[0]) * 100).toFixed(0);
                        pb3.setValue(percent);

                        $('#bar_protein').html(totalNutrient[2] + "&nbsp;g");
                        var percent = Number(totalNutrient[2] / (nutriArray[1]) * 100).toFixed(0);
                        pb4.setValue(percent);

                        show_footprint(sum/1000); //tons
                    };
                });
            });
        });

        $(function(){
            $("#bmr_button").click(function(){
                $('html,body').animate(
                    {
                        scrollTop:$('#check_bmr').offset().top
                    },
                    'slow'
                )
            });
        });
        /*
        $(document).ready(function(){
           $("#food_group").change(function(){
              var group = $(this).val();
              $.ajax({
                 url:"action.php",
                 method:"POST",
                 data:{foodGroup: group},
                  success:function(data){
                     $("#food_name").html(data);
                  }
              });
           });
        });*/

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
        var emissionData = <?php echo json_encode($emissionArray);?>;
        var calorieData =  <?php echo json_encode($calorieArray);?>;
        var nutrientData =  <?php echo json_encode($nutrientArray);?>;
        var recommendData = <?php echo json_encode($recommendArray);?>;
        var selectedRow = null;
        var imgExists = false;
        var imgName = "";

        // function add() or save() calls addIngredient()
        function addIngredient(foodName, unitChosen, weight) {
            var ingredient = foodName;
            var unit = unitChosen;
            var amount = weight;
            var metric = "";
            var emissionValue = "";
            var calorie = "";
            var finalValue = "";
            var finalCalorie = "";

            for(var i=0; i<emissionData.length;i++) {
                //console.log(dataset[i]);
                if(ingredient === emissionData[i].food_name) {
                    emissionValue = emissionData[i].value;
                } else {continue;}
            }

            for(var i=0; i<calorieData.length;i++) {
                //console.log(dataset[i]);
                if(ingredient === calorieData[i].food_name) {
                    calorie = calorieData[i].value;
                } else {continue;}
            }

            if (unit === "g") {
                finalValue = amount / 100 * emissionValue;
                finalValue = Number(finalValue).toFixed(2);
                finalValue += " kg"; // greenhouse gases

                finalCalorie = amount / 100 * calorie;
                finalCalorie = Number(finalCalorie).toFixed(2);
                metric = "g";
                finalCalorie += " kcal"; // calories
            } else if (unit === "kg") {
                finalValue = amount * emissionValue * 10;
                finalValue = Number(finalValue).toFixed(2);
                finalValue += " kg"; // greenhouse gases

                finalCalorie = amount * calorie * 10;
                finalCalorie = Number(finalCalorie).toFixed(2);
                metric = "kg";
                finalCalorie += " kcal"; // calories
            }
            return [finalValue, finalCalorie];
        }

        function checkNutrientData(nutrientDict){
            var carbDict = {};
            var fatDict = {};
            var proteinDict = {};
            var vitADict = {};
            var vitCDict = {};
            var vitEDict = {};
            var calciumDict = {};
            var totalNutrient = []; // Carbohydrates, Fats, Proteins, Vitamin A, Vitamin C, Vitamin E, Calcium
            // once we have a dictionary of Ingredient: amount (gram), go through nutrientData to get nutrient value
            for (var item in nutrientDict) {
                for (var k = 0; k < nutrientData.length; k++) {
                    if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "Carb_g")) {
                        carbDict[item] = Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "Fat_g")) {
                        fatDict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "Protein_g")) {
                        proteinDict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "VitA_mcg")) {
                        vitADict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "VitC_mg")) {
                        vitCDict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "VitE_mg")) {
                        vitEDict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    } else if ((item === nutrientData[k].food_name) && (nutrientData[k].nutrient === "Calcium_mg")) {
                        calciumDict[item] =  Number(nutrientDict[item] / 100 * nutrientData[k].value).toFixed(2);
                    }
                }
            }
            // totalNutrient; Carbohydrates, Fats, Proteins, Vitamin A, Vitamin C, Vitamin E, Calcium
            var carbSum = 0;
            var fatSum = 0;
            var proteinSum = 0;
            var vitASum = 0;
            var vitCSum = 0;
            var vitESum = 0;
            var calciumSum = 0;
            for (var item in carbDict){ carbSum += parseFloat(carbDict[item])};
            for (var item in fatDict){ fatSum += parseFloat(fatDict[item])};
            for (var item in proteinDict){ proteinSum += parseFloat(proteinDict[item])};
            for (var item in vitADict){ vitASum += parseFloat(vitADict[item])};
            for (var item in vitCDict){ vitCSum += parseFloat(vitCDict[item])};
            for (var item in vitEDict){ vitESum += parseFloat(vitEDict[item])};
            for (var item in calciumDict){ calciumSum += parseFloat(calciumDict[item])};

            totalNutrient.push(Number(carbSum).toFixed(2), Number(fatSum).toFixed(2), Number(proteinSum).toFixed(2),
                Number(vitASum).toFixed(2), Number(vitCSum).toFixed(2),Number(vitESum).toFixed(2), Number(calciumSum).toFixed(2));

            /*document.getElementById("total_nutrient").innerHTML = "Carbohydrates " + totalNutrient[0] +"g, " +
                "Fats " + totalNutrient[1] + "g, " + "Proteins " + totalNutrient[2] + "g, ";
            document.getElementById("total_nutrient2").innerHTML = "Vitamin A " + totalNutrient[3] +"mcg, " +
                "Vitamin C " + totalNutrient[4] +"mg, " + "Vitamin E " + totalNutrient[5] + "mg, " +
                "Calcium " + totalNutrient[6] +"mg"; */
            document.getElementById("total_result").style.display="block";

            return totalNutrient;
        }

        function show_footprint(totalGas){
            //in tonnes
            document.getElementById("tree_image").style.display="block";
            document.getElementById("img_tree").style.display="block";
            imgExists = true;

        }

        function onSelected(id){
            document.getElementById(id).style.visibility ="hidden";
        }

        /* add ingredient
        function add(){
            valid = validateInput();
            if (valid === true) {
                addIngredient();
                insertRecord(ingGroup, ingredient, amount, metric, finalValue, finalCalorie);
                valid = false;
                resetForm();
                selectedRow = null;
                rowExists = true;
                checkRow();
            }

        }*/
        /*
        function validateInput() {
            ingGroup = document.getElementById("food_group").value;
            ingredient = document.getElementById("food_name").value;
            amount = parseInt(document.getElementById("amountInput").value);
            unit = document.getElementById("unit").value;

            // validate and show error message
            if (ingGroup==""){
                isValid=false;
                document.getElementById("groupValidationError").style.visibility ="visible";
            } else if (ingredient==""){
                isValid=false;
                document.getElementById("ingredientValidationError").style.visibility ="visible";
            } else if ((!(amount>0)) || unit=="") {
                isValid=false;
                document.getElementById("amountValidationError").style.visibility ="visible";
            } else  {
                isValid = true;
                if (document.getElementById("groupValidationError").style.visibility === "visible")
                    document.getElementById("groupValidationError").style.visibility ="hidden";
                else if (document.getElementById("ingredientValidationError").style.visibility === "visible")
                    document.getElementById("ingredientValidationError").style.visibility ="hidden";
                else if (document.getElementById("amountValidationError").style.visibility === "visible")
                    document.getElementById("amountValidationError").style.visibility ="hidden";
            }
            return isValid;
        }*/

        function checkRow () {
            if (rowExists)
            {
                document.getElementById("calculate_button").style.visibility = "visible";

            }
            else {
                document.getElementById("calculate_button").style.visibility = "hidden";
                document.getElementById("total_result").style.display = "none";
                document.getElementById("footprint_image").style.display = "none";
                document.getElementById(imgName).style.display = "none";
                imgExists = false;
            }
        }


        function calculate_calories() {
            //valid2 = validateInput2();
            //if(valid2==true) {
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
                console.log(bmr);
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

        //}

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
                //console.log(userAge);
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
            //console.log(genderArray);
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

        /*
        function validateInput2() {
            male = document.getElementById("male").value;
            female = document.getElementById("female").value;
            height = parseInt(document.getElementById("height").value);
            weight = parseInt(document.getElementById("weight").value);
            age = parseInt(document.getElementById("age").value);
            activity = document.getElementById("activity").value;

            // validate and show error message
            if (!(document.getElementById('male').checked || document.getElementById('female').checked)){
                isValid2=false;
                document.getElementById("genderValidationError").style.visibility ="visible";
            } else if (!(height > 0)){
                isValid2=false;
                document.getElementById("heightValidationError").style.visibility ="visible";
            } else if (!(weight > 0)) {
                isValid2=false;
                document.getElementById("weightValidationError").style.visibility ="visible";
            } else if (!(age > 0)) {
                isValid2=false;
                document.getElementById("ageValidationError").style.visibility ="visible";
            } else if (activity=="") {
                isValid2=false;
                document.getElementById("activityValidationError").style.visibility ="visible";
            } else  {
                isValid2 = true;
                if (document.getElementById("genderValidationError").style.visibility === "visible")
                    document.getElementById("genderValidationError").style.visibility ="hidden";
                else if (document.getElementById("heightValidationError").style.visibility === "visible")
                    document.getElementById("heightValidationError").style.visibility ="hidden";
                else if (document.getElementById("weightValidationError").style.visibility === "visible")
                    document.getElementById("weightValidationError").style.visibility ="hidden";
                else if (document.getElementById("ageValidationError").style.visibility === "visible")
                    document.getElementById("ageValidationError").style.visibility ="hidden";
                else if (document.getElementById("activityValidationError").style.visibility === "visible")
                    document.getElementById("activityValidationError").style.visibility ="hidden";
            }
            return isValid2;
        }

        // validate user input for Amount
        function isInputNumber(evt){
            onSelected("amountValidationError");
            var ch = String.fromCharCode(evt.which);
            if(!(/[0-9]/.test(ch))) {
                evt.preventDefault();
            }
        }

        function save(){
            valid = validateInput();
            if (valid === true) {
                addIngredient();
                selectedRow.cells[0].innerHTML = ingGroup;
                selectedRow.cells[1].innerHTML = ingredient;
                selectedRow.cells[2].innerHTML = amount + metric;
                selectedRow.cells[3].innerHTML = finalValue;
                selectedRow.cells[4].innerHTML = finalCalorie;
                valid = false;
                resetForm();
                selectedRow = null;
                document.getElementById("save_button").style.visibility = "hidden";
            }
        }*/

        /*
        function insertRecord(grp,ing,amt,met,val,cal){
            // get the table by id, create a new rows and cell and set values into cell
            var table = document.getElementById("table").getElementsByTagName('tbody')[0];
            var newRow = table.insertRow(table.length);
            cell1 = newRow.insertCell(0);
            cell1.innerHTML = grp; //ingredient
            cell2 = newRow.insertCell(1);
            cell2.innerHTML = ing; //ingredient
            cell3 = newRow.insertCell(2);
            cell3.innerHTML = amt + met; //amount
            cell4 = newRow.insertCell(3);
            cell4.innerHTML = val; //value
            cell5 = newRow.insertCell(4);
            cell5.innerHTML = cal; //calories
            cell6 = newRow.insertCell(5);
            cell6.innerHTML = '<a onClick="onEdit(this)"><i class="fa fa-pencil fa-lg"></i></a>&nbsp;&nbsp;&nbsp;' +
                '<a onClick="onDelete(this)"><i class="fa fa-trash fa-lg"></i></a>';
        }

        // when Edit is clicked on
        function onEdit(td) {
            appear=true;
            show_hide();
            selectedRow = td.parentElement.parentElement; //return corresponding row

            document.getElementById("food_group").value = selectedRow.cells[0].innerHTML;
            // force an onchange event
            $("#food_group").trigger("change");


            document.getElementById("food_name").value = selectedRow.cells[1].innerHTML;
            //document.getElementById("food_name").setAttribute('value',selectedRow.cells[1].innerHTML);

            var cellAmount = selectedRow.cells[2].innerHTML;
            var returnAmount = "";
            var returnUnit = "";
            for (var i = 0; i < cellAmount.length; i++){
                if (cellAmount.charAt(i) != "k" && cellAmount.charAt(i) != "g") {
                    returnAmount += cellAmount.charAt(i);
                }
                else {
                    if (cellAmount.charAt(i) == "k") {
                        returnUnit = "kg";
                        break;
                    } else {
                        returnUnit="g";
                        break;
                    }
                }
            }
            document.getElementById("amountInput").value = returnAmount;
            document.getElementById("unit").value = returnUnit;
        }

        // delete row
        function onDelete(td){
            row = td.parentElement.parentElement;
            document.getElementById("table").deleteRow(row.rowIndex);
            resetForm();
            var x = document.getElementById("table").rows.length;
            if (x < 2) {
                rowExists = false;
            }
            checkRow();
        }
        */
        /*
        function show_hide() {
            if (appear === true){
                document.getElementById("save_button").style.visibility ="visible";
            }
        }

        // reset ingredient form
        function resetForm() {
            document.getElementById("food_group").value = "";
            document.getElementById("food_name").value = "";
            document.getElementById("amountInput").value = "";
            document.getElementById("unit").value = "";
            selectedRow = null;
        }*/
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
                <li><a href="carbon_footprint.php">Carbon Footprint</a></li>
                <li><a href="meal_plan.php" class="active-page">Meal Planning</a></li>
                <li><a href="facts.html" >Facts</a></li>
                <li><a href="about_us.html">About Us</a></li>
            </ul>
        </nav>
    </header>
    <div class="breadcrumb container">
        <a href="index.html">Home</a>&nbsp; >&nbsp;
        <span>Meal Planning</span>
    </div>
    <!-- Banner -->
    <div class="container">
        <div id="hamburgerBox"></div>
        <div id="hamburgerBtn">&#9776 </div>
    </div>
    <div class="container">
        <div id="mealBanner">
                <br><br><br><br>
                <header class="major">
                    <h3 style="color:#ffffff; font-weight: bold;">Meal Planning</h3>
                    <p style="color: #ffffff">Eat healthy with eco-friendly meals</p>
                </header>
        </div>
    </div>
    <br>
    <div class="container">
        <nav id="navBar">
            <div class="nav-brand">
                <form method="post" id="bmr_calculator_form" style="display:block;">
                    <p>STEP 1.<br>Check your daily energy requirements</p>
                    <div id="form-group2" class="form-group2">
                        <p class="bmr_form">Gender</p><br>
                            <div class="first_label" style="display: inline-block;">
                                <input class="first_label auto_save" type="radio" id="male" name="gender" checked/>
                                <label for="male" style="color:#ffffff;">Male</label>
                                <input class="auto_save" type="radio" id="female" name="gender" />
                                <label for="female" style="color:#ffffff;">Female</label>
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
                        <br><br>
                        <input type="submit" name="submit" class="button alt" style="background-color: #ffffff" id="calories_button" value="SAVE PROFILE" />
                        <hr class="major" />
                    </div> <!--div form-group-->
                </form>
            </div>
            <div class="result2" id="total_result2" style="display:none;">
                <p style="display: inline-block; margin-bottom:3px;color: black;">YOUR PROFILE</p><p id="result_bmr" style="display: inline-block; margin-bottom:5px;"></p>
                <ul class="actions">
                    <li><a id="return_button" style="background-color: #ffffff" class="button alt">EDIT PROFILE</a></li>
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
                    <p style="display: inline-block; margin: 0;">Carb&nbsp;</p><span class='glyphicon glyphicon-info-sign my-tooltip' title="Carbohydrates"></span><br>
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

            </div>
        </nav>

    </div>
    <!--
    <div class="container">
        <p class="align-left"></p>
        <div class="row">
            <div class="7u">

                <div id="form-group2" class="form-group2">
                    <p class="bmr_form">GENDER</p>
                    <input type="radio" id="male" name="gender" value="male" onchange=onSelected("genderValidationError")>
                    <label class="first_label" for="male">Male</label>
                    <input type="radio" id="female" name="gender" value="female" onchange=onSelected("genderValidationError")>
                    <label class="second_label" for="female">Female</label>
                    <div class="validation-error" style="visibility:hidden;" id="genderValidationError">Please select your gender</div>

                    <p class="bmr_form">HEIGHT</p>
                    <input class="input_height" type="text" id="height" name="height" maxlength="3" pattern="\d{3}" placeholder="cm" onkeypress="isInputNumber(event)"
                           onchange=onSelected("heightValidationError")>
                    <div class="tooltip"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                        <span class="tooltiptext">Round to the nearest integer</span>
                    </div>
                    <div class="validation-error" style="visibility:hidden;" id="heightValidationError">Please enter between 1 and 999</div>

                    <p class="bmr_form">WEIGHT</p>
                    <input class="input_weight" type="text" id="weight" name="weight" maxlength="3" pattern="\d{3}" placeholder="kg" onkeypress="isInputNumber(event)"
                           onchange=onSelected("weightValidationError")>
                    <div class="tooltip"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                        <span class="tooltiptext">Round to the nearest integer</span>
                    </div>
                    <div class="validation-error" style="visibility:hidden;" id="weightValidationError">Please enter between 1 and 999</div>

                    <p class="bmr_form">AGE</p>
                    <input class="input_age" id="age" name="age" type="text" maxlength="2" onkeypress="isInputNumber(event);" placeholder="Enter age"
                           onchange=onSelected("ageValidationError")>
                    <div class="tooltip"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                        <span class="tooltiptext">Enter between 1 and 99</span>
                    </div>
                    <div class="validation-error" style="visibility:hidden;" id="ageValidationError">Please enter between 1 and 99</div>

                    <p class="bmr_form">ACTIVITY</p>
                    <select class="input_activity" name="activity" id="activity" onchange=onSelected("activityValidationError")>
                        <option class="input_activity" value="" disabled selected>Select activity level</option>
                        <option class="input_activity" value="sedentary">Sedentary: little to no exercise</option>
                        <option class="input_activity" value="light">Light: exercise 1-3 times per week</option>
                        <option class="input_activity" value="moderate">Moderate: exercise 4-5 times per week</option>
                        <option class="input_activity" value="veryActive">Very active: intense exercise 6-7 times per week </option>
                        <option class="input_activity" value="extraActive">Extra active: very intense exercise daily</option>
                    </select>
                    <div class="extra_info">Exercise: 15-30 mins of elevated heart rate activity<br>
                        Intense exercise: 45-120 mins of elevated heart rate activity<br>
                        Very intense exercise: 2+ hrs of elevated heart rate activity</div>
                    <div class="validation-error" style="visibility:hidden;" id="activityValidationError">Please select your activity level</div>
                    <ul class="actions">
                        <li><a id="calories_button" class="button alt calories" onclick="calculate_calories()">Calculate Calories</a></li>
                    </ul>
                </div>
            </div>
            <div class="5u">
                <div class="result2" id="total_result2" style="display:none;"><br>
                    <p style="display: inline-block">YOUR DAILY ENERGY REQUIREMENTS :&nbsp;</p><p id="result_bmr" style="display: inline-block"></p><p style="display: inline-block">&nbsp;kcal</p><br>
                    <p>YOUR DAILY NUTRIENT REQUIREMENTS :&nbsp;</p><p id="result_nutrient"></p><p id="result_nutrient2"></p>
                </div>
            </div>
        </div> -->


        <!--
        <div class="row">
            <div class="12u">
                <div id="form-group" class="form-group" style="display: inline-block">
                    <label for="food_group">FOOD TYPE</label>
                    <select name ="food_group" id="food_group" onchange=onSelected("groupValidationError")>
                        <option value="" disabled selected>Select</option>
                        <?php
                            while($row=mysqli_fetch_array($result)){
                        ?>
                        <option value="<?= $row['food_group']; ?>"><?= $row['food_group']; ?></option>
                        <?php } ?>
                    </select>
                    <div class="validation-error" style="visibility:hidden;" id="groupValidationError">Please select the ingredient group</div>

                    <label for="food_name">FOOD</label>
                    <select name ="food_name" id="food_name" onchange=onSelected("ingredientValidationError")>
                        <option value="" disabled selected>Select</option>
                    </select>
                    <div class="validation-error" style="visibility:hidden;" id="ingredientValidationError">Please select the ingredient</div>

                    <label for="amount">AMOUNT</label>

                    <input class="input_amount" id="amountInput" placeholder="Weight" type="text"  maxlength="3" onkeypress="isInputNumber(event);">
                    <div class="tooltip"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                        <span class="tooltiptext">Round to the nearest integer<br>(max. 999)</span>
                    </div>
                    <select name ="unit" id="unit" onchange=onSelected("amountValidationError")>
                        <option value="" disabled selected>Select</option>
                        <option value="g">g</option>
                        <option value="kg">kg</option>
                    </select>
                    <div class="tooltip"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                        <span class="tooltiptext">g (gram)<br>kg (kilogram)</span>
                    </div>
                    <div class="validation-error" style="visibility:hidden;" id="amountValidationError">This field is required</div>

                    <script>
                        // validate user input for Amount
                        function isInputNumber(evt){
                            onSelected("amountValidationError");
                            var ch = String.fromCharCode(evt.which);
                            if(!(/[0-9]/.test(ch))) {
                                evt.preventDefault();
                            }
                        }
                    </script>

                    <ul class="actions">
                        <li><a id="add_button" class="button alt add" onclick="add()">ADD</a></li>
                        <li><a id="save_button" class="button alt save" style="visibility:hidden;" onclick="save()">SAVE</a></li>
                    </ul>
                </div>

                <table class="list" id="table">
                    <caption>Recipe Listing</caption>
                    <tr>
                        <th>Ingredient Group</th>
                        <th>Ingredient</th>
                        <th>Amount</th>
                        <th>Greenhouse Gases
                            <div class="tooltip long"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                                <span class="tooltiptext long">Greenhouse gases emitted by producing a kilogram of ingredient</span>
                            </div>
                        </th>
                        <th>Calories
                            <div class="tooltip long"><i id="info" class="fa fa-info-circle" data-toggle="tooltip"></i>
                                <span class="tooltiptext long">1 kcal is the energy required to raise the temperature of 1kg of water by 1°C</span>
                            </div>
                        </th>
                        <th>Action</th>
                    </tr>
                    <tbody id="myTable">
                    </tbody>
                </table>
                <div class="align-center">
                    <ul class="actions">
                        <li><a id="calculate_button" class="button alt calculate" style="visibility:hidden;" onclick="calculate()">CALCULATE</a></li>
                    </ul>
                </div>
            </div>
        </div> -->
    <div class="container">
        <div>
            <p>STEP 2. Build your meal plan by clicking the add button for each meal tab</p><br>
        </div>
            <h4 class="align-center">YOUR MEAL PLAN</h4>
                <ul class="nav nav-tabs" >
                    <li class="active"><a data-toggle="tab" href="#breakfast_tab">BREAKFAST</a></li>
                    <li><a data-toggle="tab" href="#lunch_tab">LUNCH</a></li>
                    <li><a data-toggle="tab" href="#dinner_tab">DINNER</a></li>
                </ul>
                <div class="tab-content" style="margin-top: 10px;">
                    <div id="breakfast_tab" class="tab-pane fade in active">
                        <form method="post" id="insert_form">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="item_table">
                                    <thead>
                                    <tr>
                                        <th>Food Type</th>
                                        <th>Food</th>
                                        <th>Weight</th>
                                        <th>Unit</th>
                                        <th>Greenhouse Gases</th>
                                        <th>Calories</th>
                                        <th class="align-left"><button type="button" name="add" class="btn btn-success btn-xs add">
                                                <span class="glyphicon glyphicon-plus"></span>
                                            </button></th>
                                    </tr>
                                    </thead>
                                    <tbody id="first_table"></tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div id="lunch_tab" class="tab-pane fade">
                        <form method="post" id="insert_form_lunch">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="item_table_lunch">
                                    <thead>
                                    <tr>
                                        <th>Food Type</th>
                                        <th>Food</th>
                                        <th>Weight</th>
                                        <th>Unit</th>
                                        <th>Greenhouse Gases</th>
                                        <th>Calories</th>
                                        <th class="align-left"><button type="button" name="add" class="btn btn-success btn-xs add">
                                                <span class="glyphicon glyphicon-plus"></span>
                                            </button></th>
                                    </tr>
                                    </thead>
                                    <tbody id="lunch_table"></tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                    <div id="dinner_tab" class="tab-pane fade">
                        <form method="post" id="insert_form_dinner">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="item_table_dinner">
                                    <thead>
                                    <tr>
                                        <th>Food Type</th>
                                        <th>Food</th>
                                        <th>Weight</th>
                                        <th>Unit</th>
                                        <th>Greenhouse Gases</th>
                                        <th>Calories</th>
                                        <th class="align-left"><button type="button" name="add" class="btn btn-success btn-xs add">
                                                <span class="glyphicon glyphicon-plus"></span>
                                            </button></th>
                                    </tr>
                                    </thead>
                                    <tbody id="dinner_table"></tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            <div>
                <p>STEP 3. Click on the CALCULATE FOODPRINT button to find out your carbon footprint</p><br>
            </div>
            <div class="align-center">
                <input type="submit" name="submit" class="button alt" id="calculate_button" value="CALCULATE FOOTPRINT" />
            </div><br>

        <div class="row">
            <div class="12u align-center">
                <div class="result" id="total_result" style="display:none; padding-top: 60px;" >
                    <br>
                    <h3><span style="text-decoration: none; display: inline; border-bottom: 2px solid #44af92; color:#000000;"> &nbsp;YOUR FOOTPRINT IS&nbsp;</h3>
                    <h3><span style="text-decoration: none; display: inline; border-bottom: 2px solid #44af92; color:#000000;" id="carbon_footprint"></span></h3><br>
                    <div id="tree_image" class="tree_image" style="display:none; text-align: center;">
                        <img id="img_tree" style="display: none; text-align: center" src="images/trees.png" class="image" width="500">
                        <h3 style="display: inline-block">It takes&nbsp;</h3><h3 style="display: inline-block; font-weight:bold;" id="tree_num"></h3>
                        <h3 style="display: inline-block">trees to offset your annual footprint</h3><br>
                        <p style="margin-bottom: 0;">On average, every tree absorbs 0.07 tons of CO2 annually.</p><p style="display: inline-block">Your footprint requires&nbsp;</p><p style="display: inline-block;font-weight:bold;" id="tree_num2"></p><p style="display: inline-block">&nbsp;trees per year.</p>
                    </div>
                    <ul class="actions">
                        <li><a id="bmr_button" class="button alt bmr">Check out our recipes </a></li>
                    </ul>
                    <h4 style="display: none" class="meal_planning">CALORIES OF YOUR MEAL PLAN :&nbsp;</h4><h4 style="display: none" id="total_calories"></h4><br>
                    <h4 style="display: none" class="meal_planning">TOTAL AMOUNT OF NUTRIENT :&nbsp;</h4><br><h4 style="display: none" id="total_nutrient"></h4><h4 style="display: none" id="total_nutrient2"></h4><br>


                    <div id="footprint_image" style="display:none; text-align: center;"><!--http://www.globalstewards.org/reduce-carbon-footprint.htm-->
                        <img id="img_verylow" style="display: none; text-align: center" src="images/verylow.png" class="image" width="400">
                        <img id="img_low" style="display: none; text-align: center" src="images/low.png" class="image" width="400">
                        <img id="img_average" style="display: none; text-align: center" src="images/average.png" class="image" width="400">
                        <img id="img_littlehigh" style="display: none; text-align: center" src="images/littlehigh.png" class="image" width="400">
                        <img id="img_high" style="display: none; text-align: center" src="images/high.png" class="image" width="400">
                        <img id="img_veryhigh" style="display: none; text-align: center" src="images/veryhigh.png" class="image" width="400">
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <br>


    </div> 	<!-- 1st Container -->

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