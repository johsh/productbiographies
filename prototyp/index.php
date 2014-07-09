<?php

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Roadtrip</title>

    <link rel="stylesheet" href="resources/style.css" />
    <link rel="stylesheet" href="resources/countries.css" />

    <script src="resources/d3.min.js"></script>
    <script src="resources/colorbrewer.v1.min.js"></script> 
    <script src="resources/topojson.v1.min.js"></script>
    <script type="text/javascript" src="resources/jquery-1.10.2.min.js"></script>

  </head>

  <style>
    /*There must be at least some minor styling ;) */
    body{
      font-family: sans-serif;
      font-size: 15px;
      font-weight: 200;
      color: #555;
    }
    h1, h2, h3, h4, h5, h6{
      font-weight: 200;
      margin: 0px;
      padding: 3px;
    }
    p{
      font-weight: 200;
      margin: 0px;
      padding: 0px;
    }
    
    
    .portrait_bar{
      width:0px; /*changes interactively*/
      /*background-color:#C8BBA1;*/   /*C6FFCE*/
      white-space: nowrap;
      padding-top: 2px;
      margin-bottom: 4px;
      border-bottom: solid 2px #C8BBA1;
      }
      #portrait_value{
        font-size: 47px;
        display: block;
        color: #888888;
        margin-bottom: 2px;
      }
      #portrait_text{
        color: #aaa;
      }
      .highlightCountry{
        background-color: red;
      }

      p:hover{
       
      }
      

  </style>

  <body>
    

    <div id="wrapper">
      <img style="width:75px; height:75px; display:block; padding-bottom: 15px" class="center" src="images/icon_alive.png">
      <p class="center" id="title">CHICKEN ROADTRIP</p>
      <p class="center" id="intro">
        Farmed animals are traded around the world alive for meat production. 
        The growing distance between production location and processing location is caused by several reasons 
        such as low prices of workforce, hygiene rules <!--, dropping prices of meat proceedings in genetically modified food and 
        factory farmy and the--> or power of transnational companies as we know from sources like the 
        <i><a href='http://www.bund.net/fileadmin/bundnet/publikationen/landwirtschaft/140328_bund_landwirtschaft_meatatlas2014.pdf' target="_blank">Meat Atlas</a></i>. 
        While the results of this intense production and trading are complex, 
        we offer a tool to focus on the actual distances, that Animals travel alive and focus on Chicken, 
        since these are the ones that travel most.</p>
    
      <!--
        SELECTION DEAD OR ALIVE
      -->
      <!--
            <div id="item_selection" class="center">
              <a href="#" id="select_live">
                  <img src="images/icon_alive.png" title="Chicken live" class="icon icon_main" id="icon_alive">
              </a>
              <a href="#" id="select_meat">
                <img src="images/icon_dead.png" title="Chicken meat" class="icon icon_main" id="icon_meat" style="opacity:0.3">
              </a>
              
            </div>
      -->

      <div id="info_container">
            
      
      
      <!-- BEGIN PORTRAIT -->
      <div id="portrait">
        <!--<p id="portrait_country" style="text-align:right">THIS COUNTRY</p>-->
        <p style="line-height:0.9; text-align:right">
          <span id="portrait_value" class="portrait_big">...</span>  
          <span id="portrait_text" >...</span>
        </p>  

        <!-- DOMAIN ICONS -->
        <div id="domain_selection" >
          <a href="#" id="select_production">
            <img src="images/icon_production.png" title="Production" class="icon icon_sub" id="icon_production">
          </a>
          <a href="#" id="select_price">
            <img src="images/icon_price.png" title="Price" class="icon icon_sub" id="icon_price">
          </a>    
          <a href="#" id="select_importAnimals">
            <img src="images/icon_import.png" title="Import" class="icon icon_sub" id="icon_import">
          </a> 
          <a href="#" id="select_exportAnimals">
            <img src="images/icon_export.png" title="Export" class="icon icon_sub" id="icon_export">
          </a>    
        </div>

      </div>
      <!-- END PORTRAIT -->

      <div id="statistics_line"></div>
      <!-- BEGIN BAR CHARTS -->
      <div id="statistics_container">
        <div id="statistics_country" style="margin-bottom:5px">GERMANY</div>
        <div id="slaughter" class="portrait_bar"></div>
        <div id="population" class="portrait_bar"></div>
        <div id="import" class="portrait_bar"></div>
        <div id="export" class="portrait_bar"></div>
      </div>
      <!--  END BAR CHARTS -->

    </div>



<!--
    <div class="button" id="cattle" onclick="toggleCattle()">[cattle]</div>
    <div class="button selected" id="chicken" onclick="toggleChicken()">[chicken]</div>
    <div class="button" id="pig" onclick="togglePig()">[pig]</div>
-->
    <div id="map"></div>
    <!--<div id="range_type" style="background-color:yellow; float:right">range type: maximum</div>-->


    <p class="center" style="margin-top:30px; width:750px">
      <br/>We used data from <a href="http://faostat3.fao.org/">FAOSTAT</a>. 
      All data shown refer to the year 2011. 

      <!--<br/>*If not declared differently, the unit 'head' is used.-->

      <br/>
      <br/>*If not declared differently, the unit 'head per annum' is used.
      <br/>
      <br/>
      <br/>
      Chicken Roadtrip is a student project by Anna Pawlowska, Johannes Schleith and Martin von Lupin
       <br/>Supervised by Sebastian Meier 
      <br/>University of Applied Sciences Potsdam, 2014
      <br/>
      <br/>


      </p>
            <br/>
  </div>
  <script>
  var range_by_maximum = true;
  $('#range_type').click(function(){
    range_by_maximum = !range_by_maximum;
    if(range_by_maximum){
      $(this).html("range type: maximum");
    }else{
      $(this).html("range type: log(maximum)");
    }
  });
  /*
    ## GLOBAL VARIABLES
    -------------------
*/
    var average_dist = 0;
    var selected_domain = "none"; //xxx
      var starting = true; // to show export when starting
    var selected_item = "Chickens" // or "Meat, chicken"

/*
  ## SELECTING AN ITEM ##
  ------------------------
*/
  $("#icon_alive").click(function(){
        highlight_this_item(this, "Chickens");
        return false; //prevent to scroll up
    });
  $('#icon_meat').click(function(){
        highlight_this_item(this, "Meat, chicken"); 
        return false; //prevent to scroll
    });
  
/*
  ## WHEN SELECTING A DOMAIN ##
  ------------------------
*/
  $('#icon_production').click(function(){
        highlight_this_domain(this, "production");
        colorCountry("produktbiographien_LivestockPrimeryProduction","Livestock Primary", "Meat indigenous, chicken","Production");
        return false; //prevent to scroll up after ajax
        //loadProduction();
    });
/*
  $('#select_trade').click(function(){        highlight_this_domain(this, "trade");        colorCountry("dummy");    });
*/
  $('#icon_price').click(function(){
        highlight_this_domain(this, "price");
        colorCountry("produktbiographien_annualPrices", "Producer Prices - Annual", "Meat live weight, chicken", "Price");
        return false; //prevent to scroll up after ajax
        //loadPrice();
    });
/*
  $('#select_slaughtered').click(function(){        highlight_this_domain(this, "slaughtered");        colorCountry("produktbiographien_LivestockPrimeryProduction","Livestock Primary", "Meat, chicken","Producing");        //loadSlaughtered();  });
*/
  $('#icon_import').click(function(){
        highlight_this_domain(this, "import");
        colorCountry("produktbiographien_trade","Live animals","Chickens","Import");
        return false; //prevent to scroll up after ajax
    });
  $('#icon_export').click(function(){
        highlight_this_domain(this, "export");
        colorCountry("produktbiographien_trade","Live animals", "Chickens","Export");
        return false; //prevent to scroll up after ajax
    });

  // ICON INTERACTION
  $('.icon').hover(function(){
    if( $(this).css("opacity") != 1){
      $(this).css("opacity", 0.8);  
    };    
  },function(){
    if( $(this).css("opacity") != 1){
      $(this).css("opacity", 0.3);  
    };
  });

function highlight_this_item(button, item){
    //highlights only this button
    //sets 'selected_domain'
    $('.icon_main').css("opacity", .3);
    $(button).css("opacity", 1);
    selected_item = item;
    updateBundledEdges();
    updateCountryOpacity();
 }

 function highlight_this_domain(button, domain){
    $('.icon_sub').css("opacity", .3);
    if(selected_domain==domain){
      selected_domain = "none";
    }else{
      selected_domain = domain;
      $(button).css("opacity", 1);  
    }
 }


    //PREPARE CANVAS
    d3.select(window).on("resize", throttle);

    var zoom = d3.behavior.zoom()
        .scaleExtent([1, 8])
        .on("zoom", move);

    var width = document.getElementById('map').offsetWidth-200;
    var height = width / 2;

    var topo,projection,path,svg,g;

    //DATA WHICH WILL BE LOADED LATER
    var data, livingAnimals, production, slaughter, consumption, population;

    var countries = {};

    var tooltip = d3.select("#map").append("div").attr("class", "tooltip hidden");
          //ofsets plus width/height of transform, plsu 20 px of padding, plus 20 extra for tooltip offset off mouse
      var offsetL = document.getElementById('map').offsetLeft+(width/2)+40;
      var offsetT = document.getElementById('map').offsetTop+(height/2)+20;

    /* COLORSCALE */
    var colorScaleTradeLine = d3.scale.linear()
              .domain([-1,-.75,-.5,-.25,0,.25,.5,.75,1])
              .range(colorbrewer.RdYlBu[9]);

    var colorScale;
    var colorScalePrice = ['#668f3b', '#557630', '#405b23', '#304819', '#1e330d'];
    var colorScalePrice_map = ['#1e330d','#304819','#405b23','#557630','#668f3b'];

    var colorScaleAnimals = ['#cfbda5', '#ad9f8b', '#837969', '#675f52', '#484339'];
    var colorScaleAnimals_map = ['#484339','#675f52','#837969','#ad9f8b','#cfbda5'];//['#cfbda5', '#ad9f8b', '#837969', '#675f52', '#484339'];



    setup(width,height);

    loadWorld();
    loadData2();
    getDataFromDatabase("Germany");



      var portrait_production_value = 0;
      var portrait_production_price = 0;
      var portrait_import_value = 0;
      var portrait_export_value = 0;
      var portrait_trade_value = 0;
    
     function getDataFromDatabase(c){
       
        $.ajax({
          type: 'GET',
          url: 'getTradeData.php',
          data: {
            country : c,
            showCattle : showCattle,
            showChickens : showChicken,
            showPigs : showPig            
          },

          success: function(data) {
            var json = JSON.parse(data);
            var c = json.country;
            var p = json.population;
            var i = json.chickens_import;
            var e = json.chickens_export;
            var slaughter = json.chickens_per_year_px;
            
            portrait_production_value = json.chickens_per_min;

            portrait_price_value = Math.round((json.price / 1000)*100)/100;
              if(portrait_price_value==0){
                portrait_price_value="not available";
              }
            portrait_import_value = Math.round(json.average_import);
            portrait_export_value = Math.round(json.average_export);
            portrait_trade_value = json.total_reporters;

             if(selectedCountry=="none"){
                slaughter = 0;
             }


            adjust_bar_value_and_width("#slaughter", "Production", slaughter, json.chickens_per_year);
            adjust_bar_value_and_width("#population", "Population", json.population_px, p);
            adjust_bar_value_and_width("#import", "Import", json.chickens_import_px, i);
            adjust_bar_value_and_width("#export", "Export", json.chickens_export_px, e);

            if(selectedCountry=="none"){
              $('#population').html(" ");
              $('#import').html(" ");
              $('#export').html(" ");
              $('#slaughter').text("to see statistics");
            }
            
            

            write_portrait(selectedCountry);
            
            if(starting){
              //show export when refreshing the page
              highlight_this_domain('#icon_export', "export"); //xxx
              colorCountry("produktbiographien_trade","Live animals", "Chickens","Export");
              starting = false;
            }
          }             
        });  
        
    }

            function write_portrait(country){
              if(country!="none"){
                //COUNTRY SELECTED
                set_portrait_country(country);
                if(selected_domain!="none"){
                  //DOMAIN SELECTED
                  if(selected_domain=="production"){set_portrait_text("animals per minute");set_portrait_value(portrait_production_value);}
                  if(selected_domain=="price"){set_portrait_text("USD/kilo");set_portrait_value(portrait_price_value);}
                  if(selected_domain=="import"){set_portrait_text("kilometer per import");set_portrait_value(portrait_import_value);}
                  if(selected_domain=="export"){set_portrait_text("kilometer per export");set_portrait_value(portrait_export_value);}

                }else{
                  //NO DOMAIN SELECTED
                  set_portrait_text("trade partners");
                  set_portrait_value(portrait_trade_value);  
                }
              }else{
                //NO COUNTRY SELECTED
                set_portrait_country("Select a country");
                set_portrait_text("&nbsp;");
                set_portrait_value("&nbsp;");
              }
            }


            function set_portrait_country(this_c){
              this_c = this_c.toUpperCase();
              $('#portrait_country').html(this_c); 
              $('#statistics_country').html(this_c + "<span style='color:#ccc'>*</span>"); 
            }
            function set_portrait_text(text){
              $('#portrait_text').html(text); 
            }

            function set_portrait_value(value){
              $('#portrait_value').html(value); 
            }

            /*
            function set_portrait(number, text){
              $('#portrait_text').html(text); 
              $('#portrait_data_distance').html(number);
              
            }
            */

thousandFormat = d3.format("0,000");

            function adjust_bar_value_and_width(this_id, text, px, real_value){
              $(this_id)
                .html(thousandFormat(real_value) + " " + "<span style='color:#aaa'>"+text+"</span>")
                .animate({width: px + "px"},500);
            }
            


    /*----------------------------
      ----------------------------
      functions loading stuff
      ----------------------------
      ----------------------------*/

    /*
        SETUP():
        INITS PROJECTION + SVG ELEMENTS
    */
    var λ = d3.scale.linear()
      .domain([0, width])
      .range([-180, 180]);

    var φ = d3.scale.linear()
      .domain([0, height])
      .range([90, -90]);

    function setup(width,height){
      //projection = d3.geo.equirectangular()
      //projection = d3.geo.orthographic()
      projection = d3.geo.mercator()
        .translate([-10, 60])
        .scale(width / 2 / Math.PI)
        ;

      path = d3.geo.path()
          .projection(projection);

      svg = d3.select("#map").append("svg")
          .attr("width", width)
          .attr("height", height)
          .append("g")
          .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
          .call(zoom)
          /*.on("drag", function() {
            console.log("drafg");
            var p = d3.mouse(this);
            projection.rotate([λ(p[0]), φ(height/2)]);
            svg.selectAll("path").attr("d", path);
          });*/

          ;

      svg.append("rect")
        .attr({
          x:-width,
          y:-height,
          width: 2*width,
          height: 2*height,
        })
        .style("fill", "#EBF8FF")
        .on("click", function(){
          //disable stuff
          selectedCountry = "none";   
          getDataFromDatabase(selectedCountry);
          updateBundledEdges();
          updateCountryOpacity();
        })
        ;

      g = svg.append("g");

    }

    /*
        LOAD WORLD()
        LOADS WORLD DATA + COUNTRY COORDINATES 
    */

    function loadWorld(){

      //LOAD WORLD DATA
      d3.json("data/world-topo.json", function(error, world) {
        var _topo = topojson.feature(world, world.objects.countries).features;
        topo = _topo;
        draw(topo);

        d3.tsv("data/countries_all_faoNames.csv", function(error, _centroids){
          //SKIP COUNTRIES FOR WHICH NO REGION IS DEFINED
          console.log(_centroids[0].REGION);
          _centroids = _centroids.filter(function(c){
            return  c.REGION != undefined && 
                    c.REGION != " " && 
                    c.REGION != "";
          })

          //GENERATE COUNTRY-HIERARCHY FOR EDGE BUNDLING

          //MAIN COUNTRY LIST
          countries = {};

          //ADD world AS ROOT
          var world = countries["World"] = {
              name: "World", 
              coordinates: [-39, 31], 
              children: [],
              depth: 1,
          }

          //ADD CONTINENTS
          var asia = countries["Asia"] = {
              name: "Asia", 
              coordinates: [-39, 31], 
              parent: world,
              children: [],
              depth: 3,
              coordinates: [43.269327, 52.337546],
            }
          var europe = countries["Europe"] = {
              parent: world,
              name: "Europe", 
              children: [],
              depth: 3,
              coordinates: [11.725251, 47.407291],
            }
          var southAmerica = countries["South America"] = {
              parent: world,
              name: "South America", 
              children: [],
              depth: 3,
              coordinates: [-39.954439, 3.263399],
            }
          var northAmerica = countries["North America"] = {
              parent: world,
              name: "North America", 
              children: [],
              depth: 3,
              coordinates: [-71.741284, 30.936133],
            }
          var africa = countries["Africa"] = {
              parent: world,
              name: "Africa", 
              children: [],
              depth: 3,
              coordinates: [-4.894734, -8.990032],
            }
          var oceania = countries["Australia and Oceania"] = {
              parent: world,
              name: "Australia and Oceania",
              children: [],
              depth: 3,
              coordinates: [99.264314, -24.435121]
            }

          world.children = [asia, europe, southAmerica, northAmerica, africa, oceania];

          //STORE CONTINENT-REGIONS HERE:
          regions = [];

          //FOR EACH COUNTRY
          _centroids.forEach(function(_c){

            //STORE IT ...
            
            var thisName;
            if(_c.NAME_IN_FAO_DATA != "no"){
              //IS THERE A NAME IN THE SPECIAL FAO COLUMN?
              thisName = _c.NAME_IN_FAO_DATA;
            }else{
              thisName = _c.SHORT_NAME;
            }
            var node = countries[thisName] = {
              name: thisName, 
              coordinates: [parseFloat(_c.LONG), parseFloat(_c.LAT)], 
              children: [],
              depth: 4,
            }

            //GET PARENTING REGION ...
            var parent = countries[_c.REGION];
            //IF NOT AVAILABLE SET IT ...
            if (!parent){
              console.log("parent undefined");
              parent = countries[_c.REGION] = {
                name: _c.REGION, 
                coordinates: [0,0], 
                children: [],
                depth: 3, 
                parent: world,
              }
              world.children.push(parent);
              regions.push(parent);
            }

            //ADD TO REGION's COORDINATES
            /* already set above
            parent.coordinates[0]+=node.coordinates[0];
            parent.coordinates[1]+=node.coordinates[1];
            */

            //ADD PARENT TO NODE, AND NODE TO PARENT
            node.parent = parent;
            node.parent.children.push(node);
          });

          //GET CENTER OF REGION's COORDINATES
          /* already set above
          regions.forEach(function(r){
            r.coordinates[0] /= r.children.length;
            r.coordinates[1] /= r.children.length;
          });
          */

          //WHEN FINISHED LOAD DATA
          loadData2();
        });
      });
    }

    /*
      LOADS THE DATA
    */
    var dataCombined = {};

    function loadData2(){
      // LIKE loadData()
      // JUST CHANGED THE SOURCE tradeDataChicken

      /* LIVING CHICKEN DATA */
      d3.csv("data/matrixChickenDeadAlive.csv", function(error, _data){ 
        data = _data;
        console.log("matrixChickenDeadAlive.csv loaded");

        /* GET IMPORT/EXPORT */
        data.forEach(function(d){
          var _source = simplifyName(d.Source);
          var _target = simplifyName(d.Target);          
          if (dataCombined[_source] == undefined){
            dataCombined[_source] = {};
            dataCombined[_source][_target] = d;
          } else {

            if (dataCombined[_source][_target] == undefined)
              dataCombined[_source][_target] = d;

            if (d.Valuetype.indexOf("Import") != -1)
              dataCombined[_source][_target].Import = d.Value;
            else if (d.Valuetype.indexOf("Export") != -1)
              dataCombined[_source][_target].Export = d.Value;
            else {
              console.log("sth wrong w/ data");
              console.log (d);
            }
          }
        });

        _dataCombined = [];
        Object.keys(dataCombined).forEach(function(k){
          Object.keys(dataCombined[k]).forEach(function(_k){

            if (dataCombined[k][_k].Import != undefined && 
                dataCombined[k][_k].Export != undefined){

              dataCombined[k][_k].Valuetype = "ImpMinusExp";
              dataCombined[k][_k].Value = parseFloat(dataCombined[k][_k].Import)-parseFloat(dataCombined[k][_k].Export);

              dataCombined[k][_k].Value /= Math.max(parseFloat(dataCombined[k][_k].Import),
                                                    parseFloat(dataCombined[k][_k].Export));
            } else if (dataCombined[k][_k].Import != undefined){
              dataCombined[k][_k].Value = 1;
            } else {
              dataCombined[k][_k].Value = -1
            }

            _dataCombined.push(dataCombined[k][_k]);
          })
        })
        data = _dataCombined;

        clickCountry({properties: {name: "Germany"}});
      });

    }


    function loadData(){

      //FINALLY LOAD DATA

      /* MEAT DATA */
      /*
      d3.csv("data/dataMeat.csv", function(error, _data){ 
        data = _data;//.filter(function(d,i){return i <20;});
        console.log("livingAnimals loaded");

        updateBundledEdges("Germany");
      });
      */

      /* LIVING CHICKEN DATA */
      d3.csv("data/tradeDataChicken.csv", function(error, _data){ 
        data = _data;
        console.log("livingAnimals loaded");

        /* GET IMPORT/EXPORT */
        data.forEach(function(d){
          var _source = simplifyName(d.Source);
          var _target = simplifyName(d.Target);          
          if (dataCombined[_source] == undefined){
            dataCombined[_source] = {};
            dataCombined[_source][_target] = d;
          } else {

            if (dataCombined[_source][_target] == undefined)
              dataCombined[_source][_target] = d;

            if (d.Valuetype.indexOf("Import") != -1)
              dataCombined[_source][_target].Import = d.Value;
            else if (d.Valuetype.indexOf("Export") != -1)
              dataCombined[_source][_target].Export = d.Value;
            else {
              console.log("sth wrong w/ data");
              console.log (d);
            }
          }
        });

        _dataCombined = [];
        Object.keys(dataCombined).forEach(function(k){
          Object.keys(dataCombined[k]).forEach(function(_k){

            if (dataCombined[k][_k].Import != undefined && 
                dataCombined[k][_k].Export != undefined){

              dataCombined[k][_k].Valuetype = "ImpMinusExp";
              dataCombined[k][_k].Value = parseFloat(dataCombined[k][_k].Import)-parseFloat(dataCombined[k][_k].Export);

              dataCombined[k][_k].Value /= Math.max(parseFloat(dataCombined[k][_k].Import),
                                                    parseFloat(dataCombined[k][_k].Export));
            } else if (dataCombined[k][_k].Import != undefined){
              dataCombined[k][_k].Value = 1;
            } else {
              dataCombined[k][_k].Value = -1
            }

            _dataCombined.push(dataCombined[k][_k]);
          })
        })
        data = _dataCombined;

        clickCountry({properties: {name: "Germany"}});
      });

    }

    /*
        FILTERS DATA BY FOLLOWING CRITERIA
    */
    var selectedCountry = "Germany";

    
    // TBC var selectedYear = 2011;
    
    var showCattle = false;
    var showChicken = true;
    var showPig = false;

    function filterData(){
      return data.filter(function(d){
        return (d.Source == selectedCountry && d.Value > 0)// || d.Target == selectedCountry) 
                && (
                      (showCattle && d.Product == "Cattle") ||
                      (showChicken && d.Product == "Chickens") ||
                      (showPig && d.Product == "Pigs" ) ||
                      (d.Product == "Meat, chicken")
                    )
                ;
      })      
    }

    function filterData2(item){
      return data.filter(function(d){
        return (d.Source == selectedCountry)// || d.Target == selectedCountry) 
                && (d.Product == item);
      })      
    }

    /* PRICING */

    var pricing;
    var maxPrice = -1;
    function loadPrice(){

     d3.csv("data/pricingChicken.csv", function(error, _data){
     // d3.csv("data/productionChicken.csv", function(error, _data){
        pricing = _data;//.filter(function(d,i){return i <20;});
        console.log(pricing);
        pricing.forEach(function(d){
          maxPrice = Math.max(maxPrice, parseInt(d.Value));
        });
        console.log(maxPrice);

        pricing.forEach(function(d){
          d3.selectAll("."+simplifyName(d.Source)).style("fill", "rgb("+parseInt(255*(1-parseInt(d.Value)/maxPrice))+","+
                                                                        parseInt(255*(1-parseInt(d.Value)/maxPrice))+","+
                                                                        parseInt(255*(1-parseInt(d.Value)/maxPrice))+")");
        });
        
        //updateBundledEdges("Germany");
      });
    }

    var maxValueCountryColor;
    function colorCountry(data_table, domain, item, element){
      //check if any sub_icon is selected
      //if not, color everything the same color
      if(selected_domain!="none"){

      //TRY TO GET COUNTRY FILL DATA FROM DATA BASE
      
          $.ajax({
          type: 'GET',
          url: 'getColorData.php',
          dataType: 'json',
          data: {
              data_table : data_table,
              domain : domain,
              item : item,
              element : element
          },

          success: function(_data) {
            //var json = JSON.parse(_data);
            //var c = json.country;
            var data = _data;
            maxValueCountryColor = -1; //needs to be resetted every time
            
            data.forEach(function(d){
              maxValueCountryColor = Math.max(maxValueCountryColor, d.Value);
            });


            d3.selectAll(".country").style("fill","#e0e0e0"); //"deselect" all countries
            data.forEach(function(d){


                var color;
                if (item == "Meat live weight, chicken") {
                  
                  color = colorScalePrice_map[proportion(d.Value,maxValueCountryColor,0,4)];
                }
                else {
                  color = colorScaleAnimals_map[proportion(d.Value,maxValueCountryColor,0,4)];
                  
                }

                d3.selectAll("."+simplifyName(d.Country)).style("fill", color);

              });
           drawLegend(item != "Meat live weight, chicken"); 
          } 

        });


 
        }else{
          //if sub_icon is deselected no country gets colored
          d3.selectAll(".country").style("fill","#b7b7b7");
     
        }
        write_portrait(selectedCountry);
      }

    function proportion(value,max,minrange,maxrange) {
      return Math.round(((max-value)/(max))*(maxrange-minrange))+minrange;
    }

    /* PRODUCTION */

    var production;
    var maxProduction = -1;
    function loadProduction(){

    d3.csv("data/productionChicken.csv", function(error, _data){
    //  d3.csv("data/slaughteredChicken.csv", function(error, _data){
        production = _data;//.filter(function(d,i){return i <20;});
        
        production.forEach(function(d){
          maxProduction = Math.max(maxProduction, parseInt(d.Value));
        });
        console.log(maxProduction);

        production.forEach(function(d){
          d3.selectAll("."+simplifyName(d.Source)).style("fill", "rgb("+parseInt(255*(1-parseInt(d.Value)/maxProduction))+","+
                                                                        parseInt(255*(1-parseInt(d.Value)/maxProduction))+","+
                                                                        parseInt(255*(1-parseInt(d.Value)/maxProduction))+")");
        });
        
        //updateBundledEdges("Germany");
      });
    }

    /* SLAUGHTERED / POPULATION */

    var slaughteredChicken;
    var _production = {};
    var maxSlaughtered = -1;
    function loadSlaughtered(){

    d3.csv("data/productionChicken.csv", function(error, _data){
        _data.forEach(function(d){
          _production[d.Source] = d;
        });

        d3.csv("data/slaughteredChicken.csv", function(error, _data){
          slaughteredChicken = _data;
          
          slaughteredChicken.forEach(function(d){
            maxSlaughtered = Math.max(maxSlaughtered, parseInt(d.Value));
          });        

          
          slaughteredChicken.forEach(function(d){
            //console.log(parseInt(population[d.Source].Value));

            var tmp = parseInt(_production[d.Source].Value) / parseInt(d.Value);
            console.log(d.Source + ": "+ _production[d.Source].Value + " / " + d.Value + " = " + tmp);

            d3.selectAll("."+simplifyName(d.Source)).style("fill", "rgb("+parseInt(255*tmp)+","+
                                                                          parseInt(255*tmp)+","+
                                                                          parseInt(255*tmp)+")");
          });
          
          //updateBundledEdges("Germany");
        });
      });
    }


    /*----------------------------
      ----------------------------
      functions toggling stuff
      ----------------------------
      ----------------------------*/

    /* 
        TOGGLE STUFF
    */
    function toggleCattle(){
      d3.selectAll("#cattle.button").classed("selected", !showCattle);
      showCattle = !showCattle;
      updateBundledEdges();
    }

    function toggleChicken(){
      d3.selectAll("#chicken.button").classed("selected", !showChicken);
      showChicken = !showChicken;
      updateBundledEdges();
    }
   
    function togglePig(){
      d3.selectAll("#pig.button").classed("selected", !showPig);
      showPig = !showPig;
      updateBundledEdges();
    }

    /*  
        IF A COUNTRY IS CLICKED ...
    */
    function clickCountry(country){
      if(selectedCountry==country.properties.name){
        //DESELECT COUNTRY WHEN DOUBLE CLICK
        selectedCountry = "none";            
      }else{
        selectedCountry = country.properties.name;
      }

      //Highlight selected Country?
      getDataFromDatabase(selectedCountry);
      updateBundledEdges();
      updateCountryOpacity();
      
    }



    /*----------------------------
      ----------------------------
      drawing functions
      ----------------------------
      ----------------------------*/

    var maxValueCattle = -1;
    var maxValueChicken = -1;
    var maxValuePig = -1;

    //EDGE BUNDLING STUFF
    var standardTension = .9;
    var line = d3.svg.line()
        .interpolate("bundle")
        //.interpolate("linear")
        .tension(standardTension)
        .x(function(d) { 
          return projection(d.coordinates)[0];
        })
        .y(function(d) { 
          return projection(d.coordinates)[1];
        })

    var bundle = d3.layout.bundle();
    var links;

    var maxImport = -1;
    var maxExport = -1;

    var zoomLineWidth = 1;
    var maxStrokeWidth = 10;

    /*
        DRAW BUNDLE EDGES():
        DRAWS BUNDLED EDGES FOR ALL DATA
        USES PACKAGE HIERARCHY
    */
    function updateBundledEdges(){
      d3.selectAll(".link").remove();
      d3.selectAll(".dot").remove();

      var _data = filterData2(selected_item);

      links = packageHierarchy(_data);

      maxImport = -1;
      maxExport = -1;

      _data.forEach(function(_d){
        if (!isNaN(_d.Import))
          maxImport = Math.max(parseFloat(_d.Import), maxImport);
        if (!isNaN(_d.Export))
          maxExport = Math.max(parseFloat(_d.Export), maxExport);
      })

              console.log("max: " + maxImport + " " + maxExport);

      g.selectAll(".trade")
        .data(links)
        .enter().append("path")
          .attr("class", function(d){
              if (d.data.Valuetype.toLowerCase().indexOf("import") != -1)
                return "link _import";
              else if (d.data.Valuetype.toLowerCase().indexOf("export") != -1)
                return "link _export";
              else
                return "link _neitherImportNorExport";
          })
          .style({
            "stroke": function(d){
              return colorScaleTradeLine(d.data.Value);
            },
            "stroke-width": function(d){ 
              //re-calc maxImport/maxExport

              //return maxStrokeWidth;

              if (d.data.Valuetype.toLowerCase().indexOf("import") != -1)
                return Math.max(.7,zoomLineWidth * maxStrokeWidth * d.data.Import/maxImport);
              else if (d.data.Valuetype.toLowerCase().indexOf("export") != -1)
                return Math.max(.7, zoomLineWidth * maxStrokeWidth*d.data.Export/maxExport);
              else {
                return Math.max(.7, zoomLineWidth * maxStrokeWidth*(parseInt(d.data.Import)+parseInt(d.data.Export))/(maxImport+maxExport));
              }
              
            },
            fill: "none",
          })
          .attr("d", function(d){
            return line(d.points);
          })
          .on("mouseover", function(d){

            //d3.select(this).style("stroke", "lightgrey");

            var mouse = d3.mouse(svg.node()).map( function(d) { return parseInt(d); } );

            var _string = d.data.Source +" to "+d.data.Target+"\n";

            if (!isNaN(d.data.Import) && d.data.Import > 0)
              _string += "\t"+thousandFormat(d.data.Import*1000)+" Import\n";
            if (!isNaN(d.data.Export) && d.data.Export > 0)
              _string += "\t"+thousandFormat(d.data.Export*1000)+" Export\n";

            tooltip
                .classed("hidden", false)
                .attr("style", "left:"+(mouse[0]+offsetL)+"px;top:"+(mouse[1]+offsetT)+"px")
                .html(_string)
          })
          .on("mouseout",  function(d,i) {
            d3.select(this).style("stroke", function(d){
              return colorScaleTradeLine(d.data.Value);
            });

            tooltip.classed("hidden", true);
          })
          .call(zoom)
          ;

          if (selectedCountry != "none" && 
              selectedCountry != undefined ||
              countries[selectedCountry] != undefined)
          {
            g.append("circle")
              .attr({
                class: "dot",
                cx: projection(countries[selectedCountry].coordinates)[0],
                cy: projection(countries[selectedCountry].coordinates)[1],
                r: 3,
              })
              .on("click", function(){
                clickCountry({properties: {name: selectedCountry}});
              })
          }
    }

    function updateCountryOpacity(){
      var o = 0.2;
      if(selectedCountry!="none"){
        o = 0.2;
      }else{
        o=1;
      }

      /* FIRST HIDE ALL COUNTRIES */
      d3.selectAll(".country")
        .transition()
        .style("opacity", o); //xxx

      data.forEach(function(d){
        if (simplifyName(d.Source) == simplifyName(selectedCountry) && d.Product == selected_item
          )
          d3.selectAll(".country."+simplifyName(d.Target))
            .transition()
            .style("opacity", 1);
      })

      d3.selectAll(".country."+simplifyName(selectedCountry))
        .transition()
        .style("opacity", 1);


    }

    /*
        PACKAGE HIERARCHY
        PACKS DATA TO LINKS
    */
    var mediterranean = {name: "Mediterranean Sea", coordinates: [14.265421, 34.224703]};
    var northAtlanticOcean = {name: "Noth Atlantic Ocean", coordinates: [-41.281455, 34.659600]};
    var middleAtlanticOcean = {name: "Middle Atlantic Ocean", coordinates: [-25.812705, 0.076026]};
    var southernOcean = {name: "Southern Ocean", coordinates: [1.178375, -60.709734]};
    var indianOcean = {name: "Indian Ocean", coordinates: [83.347455, -21.183604]};

    //var middleAtlanticOcean = {name: "Noth Atlantic Ocean", coordinates: [0.076026, -25.812705]};
    function packageHierarchy(data) {
      var links = [];

      var linksToEurope = [];
      var linksToNorthAmerica = [];
      var linksToSouthAmerica = [];
      var linksToAfrica = [];
      var linksToAsia = [];

      data.forEach(function(d){
        if (countries[d.Source] != undefined &&
            countries[d.Target] != undefined){

          var tmp = {
            data: d,
            points: []
          }

          // SOURCE COUNTRY
          tmp.points.push(countries[d.Source]);

          // IF DIST SOURCE-INBETWEEN < SOURCE-TARGET
          line.tension(standardTension);

          // INTRA CONTINENTAL
          if (countries[d.Source].parent.name == countries[d.Target].parent.name)
          {

            //line.tension(.1);
            tmp.points.push(countries[d.Target].parent);
            ;

          // EUROPE TO SOUT AMERICA
          } else if (countries[d.Source].parent.name == "Europe" && countries[d.Target].parent.name == "South America"){
          
            tmp.points.push(northAtlanticOcean);
            tmp.points.push(countries[d.Target].parent);

          // SOUTH AMERICA TO EUROPE
          } else if (countries[d.Source].parent.name == "South America" && countries[d.Target].parent.name == "Europe")
          {
            tmp.points.push(northAtlanticOcean);
            tmp.points.push(countries[d.Target].parent);

          // SOUTH AMERICA TO ASIA
          } else if (countries[d.Source].parent.name == "South America" && countries[d.Target].parent.name == "Asia")
          {

            line.tension(standardTension);
            //tmp.points.push(middleAtlanticOcean);
            tmp.points.push(southernOcean);
            tmp.points.push(indianOcean);              
            //tmp.points.push(countries[d.Target].parent);


          // EUROPE or NORTH AMERICA TO OCEANIA
          } else if ((countries[d.Source].parent.name == "Europe" || 
                     countries[d.Source].parent.name == "North America")  &&
              countries[d.Target].parent.name == "Australia and Oceania")
          {
          
            tmp.points.push(northAtlanticOcean);
            tmp.points.push(middleAtlanticOcean);
            tmp.points.push(southernOcean);
            tmp.points.push(countries[d.Target].parent);

          // OCEANIA OR ASIA TO EUROPE, NORTH AMERICA OR SOUTH AMERICA
          } else if ((countries[d.Source].parent.name == "Australia and Oceania" || 
                      countries[d.Source].parent.name == "Asia") && 
                     (countries[d.Target].parent.name == "Europe" || 
                     countries[d.Target].parent.name == "North America" ||
                     countries[d.Target].parent.name == "South America"))
          {
            
            tmp.points.push(indianOcean);
            tmp.points.push(southernOcean);
            tmp.points.push(middleAtlanticOcean);
            //tmp.points.push(northAtlanticOcean);
            tmp.points.push(countries[d.Target].parent);

          // EUROPE TO AFRICA
          } else if (countries[d.Source].parent.name == "Europe" &&
              countries[d.Target].parent.name == "Africa")
          {

            tmp.points.push(northAtlanticOcean);
            tmp.points.push(middleAtlanticOcean);  
            //tmp.points.push(mediterranean);

          // AFRICA TO NORTH AMERICA OR EUROPE
          } else if (countries[d.Source].parent.name == "Africa" && 
            (countries[d.Target].parent.name == "North America" || countries[d.Target].parent.name == "Europe")){
            
              tmp.points.push(middleAtlanticOcean);
              tmp.points.push(northAtlanticOcean);

          // EUROPE TO NORTH AMERICA OR NORTH AMERICA TO SOUTH AMERICA
          } else if (((countries[d.Source].parent.name == "Europe") &&
              countries[d.Target].parent.name == "North America") ||
              (countries[d.Source].parent.name == "North America" && 
               countries[d.Target].parent.name != "South America"))
          {
          
            tmp.points.push(northAtlanticOcean);
            tmp.points.push(countries[d.Target].parent);

          } else if (getDistance(countries[d.Source].coordinates, countries[d.Source].parent.coordinates) < 
              getDistance(countries[d.Source].coordinates, countries[d.Target].coordinates))
          {
            tmp.points.push(countries[d.Target].parent);
          }
                     

          
          tmp.points.push(countries[d.Target]);
          links.push(tmp);
        }
      })

      links.forEach(function(l){
        l.points=cleanPath(l.points);  
      });
      
      return links;
    }

    /* HELPER REMOVING NON-NECESSARY POINTS */


    /*
        DRAW()
        SIMPLY DRAWS THE MAP
    */
    function draw(topo) {

      var country = g.selectAll(".country").data(topo);

      country.enter().insert("path")
          .attr("class", function(d){ return "country " + simplifyName(d.properties.name); })
          .attr("d", path)
          .attr("id", function(d,i) { return d.id; })
          .attr("title", function(d,i) { return d.properties.name; })
          .style("fill", "#b7b7b7");

      //tooltips
      country
        .on("mousemove", function(d,i) {
          var mouse = d3.mouse(svg.node()).map( function(d) { return parseInt(d); } );
            tooltip
              .classed("hidden", false)
              .attr("style", "left:"+(mouse[0]+offsetL)+"px;top:"+(mouse[1]+offsetT)+"px")
              .html(d.properties.name)
          })
          .on("mouseout",  function(d,i) {
            tooltip.classed("hidden", true)
          })
          .on("click", function(d){ clickCountry(d); }); 
    }

    /* DRAW LEGEND */
    function drawLegend(animalNotPrice){

      d3.selectAll(".legend").remove();

      svg.append("rect")
          .attr({
            class: "legend box",
            x: 6-width/2,
            y: 6-height/2,
            width: function(){
              return 445;

              if (maxValueCountryColor == undefined) return 220;
              else return 445;
            },
            height: 70,
          })

      /* TITLE */
      svg.append("text")
        .text("Trade Lines\n")
        .attr({
          class: "legend",
          x: 10-width/2,
          y: 20-height/2,
        });

      /* IMPORT / EXPORT */
      [8,4,0].forEach(function(d,i){

        svg.append("line")
          .attr({
            class: "legend",
            x1: 21-width/2,
            y1: 34-height/2+i*16,
            x2: 21-width/2+30,
            y2: 34-height/2+i*16,
          })
          .style({
            "stroke-width": 2,
            stroke: colorbrewer.RdYlBu[9][d],
          })
          ;

        /* LABEL IMPORT */
        svg.append("text")
          .text(function(){
            if (i==0) return "Import outweights export";
            else if (i==1) return "Import equals export";
            else return "Export outweights import";
          })
          .attr({
            class: "legend",
            x: 21-width/2+36,
            y: 38-height/2+i*16,
          });

      });

      //if (maxValueCountryColor == undefined) return;

      /* TITLE */
      svg.append("text")
        .text("Country fill\n")
        .attr({
          class: "legend",
          x: 241-width/2,
          y: 20-height/2,
        });

      var boxSize = 40;
      /* COUNTRY COLORING */
      var _scale;
      if (animalNotPrice) _scale = colorScaleAnimals;//.reverse();
      else _scale = colorScalePrice;//.reverse();

      _scale.forEach(function(d,i){

        svg.append("rect")
          .attr({
            class: "legend",
            x: 241-width/2+i*boxSize,
            y: 28-height/2,
            width: boxSize,
            height: 10,
          })
          .style({
            fill: d,
          })  
      });

      /* LABEL IMPORT */
      svg.append("text")
        .text("0")
        .attr({
          class: "legend",
          x: 241-width/2,
          y: 53-height/2,
        });

      svg.append("text")
        .text(thousandFormat(maxValueCountryColor))
        .attr({
          class: "legend",
          x: 241-width/2+5*boxSize,
          y: 53-height/2,
        })
        .style({
          "text-anchor": "end",
        });

        /* NO DATA */
        svg.append("line")
          .attr({
            class: "legend",
            x1: 241-width/2,
            y1: 65-height/2,
            x2: 241-width/2+25,
            y2: 65-height/2,
          })
          .style({
            "stroke-width": 10,
            stroke: "b7b7b7",
          })
          ;

      svg.append("text")
        .text("No data available")
        .attr({
          class: "legend",
          x: 276-width/2,
          y: 70-height/2,
        })
          
    }
    drawLegend();

    /*
        RE-DRAWS THE MAP
        ... EXECUTED FOR EXAMPLE WHEN WINDOW SIZE WAS CHANGED
    */
    function redraw() {
      width = document.getElementById('map').offsetWidth-60;
      height = width / 2;
      d3.select('svg').remove();
      setup(width,height);
      draw(topo);
    }

    /*
        MOVE()
        KP WAS DIE HIER MACHT
    */

    function move(){

      var t = d3.event.translate;
      var s = d3.event.scale;  
      var h = height / 3;
      
      //t[0] = Math.min(width / 2 * (s - 1), Math.max(width / 2 * (1 - s), t[0]));
      //t[1] = Math.min(height / 2 * (s - 1) + h * s, Math.max(height / 2 * (1 - s) - h * s, t[1]));

      zoom.translate(t);
      g.style("stroke-width", 1 / s).attr("transform", "translate(" + t + ")scale(" + s + ")");
      //g.style("stroke-width", 1 / s).attr("transform", "scale(" + s + ")");

      /* ROTATE GLOBE */
      /*var p = d3.mouse(this);
      projection.rotate([λ(p[0]), φ(height/2)]);
      svg.selectAll("path").attr("d", path);*/

      //zoomLineWidth = 1-s/10;
      //updateBundledEdges();
    }

    var throttleTimer;
    function throttle() {
      window.clearTimeout(throttleTimer);
        throttleTimer = window.setTimeout(function() {
          //redraw();
        }, 200);
    }

    /*----------------------------
      ----------------------------
                helper
      ----------------------------
      ----------------------------*/

    var cleanFactor = 1.6;//3.8;
    function cleanPath(points){
      //return points;

      var _points = [points[0]];

      var p1 = points[0];
      var p2 = points[1];
      var p3 = points[2];

      for (var i=1;i<points.length-1;i++){
        if (squared(getDistance(p1.coordinates, p2.coordinates))
            +squared(getDistance(p2.coordinates, p3.coordinates))
                < squared(getDistance(p1.coordinates, p3.coordinates)*cleanFactor))
        {
          _points.push(p2);
          p1=points[i];
          p2=points[i+1];
          p3=points[i+2];
        } else {
          p2=p3;
          p3=points[i+1];
        }
      }
      _points.push(points[points.length-1]);

      return _points;
    }

    var replaceThis = [" ", "_", "-", ",", "'", "ç", "å", "é"];
    function simplifyName(a){
      var _a = a.toLowerCase();

      replaceThis.forEach(function(str){
        _a = replaceAll(str, "_", _a);
      })

      _a = _a.replace("(","-");
      _a = _a.replace(")","-");

      return _a;
    }

    //find = this has to be replaced...
    //replace = with that str
    //str = in this str, please
    function replaceAll(find, replace, str) {
      return str.replace(new RegExp(find, 'g'), replace);
    }

    function getDistance(p1, p2) {
      return Math.sqrt(squared(p1[0]-p2[0]) + squared(p1[1]-p2[1]));
    }

    function squared(x){
      return x*x;
    }



  </script>
  </body>
</html>