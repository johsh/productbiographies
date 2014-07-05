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
    <script src="resources/topojson.v1.min.js"></script>
    <script type="text/javascript" src="resources/jquery-1.10.2.min.js"></script>


  </head>

  <style>
    /*There must be at least some minor styling ;) */
    body{
      font-family: sans-serif;
      font-size: 14px;
      font-weight: 200;
      color: #333;
    }
    h1, h2, h3, h4, h5, h6, p{
      font-weight: 200;
      margin: 0px;
      padding: 3px;
      color: #666;
    }
    .portrait_container{
      width: 100%;
      min-height: 50px;
      border: dotted thin #333;
    }
    
    .portrait_bar{
      width:100px; /*changes interactively*/
      height:15px;
      background-color:#C6FFCE;
      white-space: nowrap;
      }
  </style>

  <body>
    

    <div id="wrapper">
    <p class="center">CHICKEN ROADTRIP</p>
    <div id="domain_selection" class="center">
        <a href="#" id="select_production">Production</a>
        <a href="#" id="select_trade">Trade</a>
        <a href="#" id="select_price">Price</a>    
        <a href="#" id="select_slaughtered">Slaughtered</a>    
    </div>
    <br/>
    <div id="portrait" class="center">
      <p id="portrait_country">THIS COUNTRY</p>
        <p id="portrait_text"></p>
        <p id="portrait_data_distance" class="portrait_big">...</p>  
    </div>


<!--
    <div class="button" id="cattle" onclick="toggleCattle()">[cattle]</div>
    <div class="button selected" id="chicken" onclick="toggleChicken()">[chicken]</div>
    <div class="button" id="pig" onclick="togglePig()">[pig]</div>
-->
    <div id="map"></div>
    <p class="center">src: FAOSTAT</p>
  </div>
  <script>

  /*
    ## GLOBAL VARIABLES
    -------------------
*/
    var average_dist = 0;
    var selected_domain = "production"

/*
  ## WHEN SELECTING A DOMAIN ##
  ------------------------
*/
  $('#select_production').click(function(){
        highlight_this_button(this, "production");
        loadProduction();
    });
  $('#select_trade').click(function(){
        highlight_this_button(this, "trade");
    });
  $('#select_price').click(function(){
        highlight_this_button(this, "price");
        loadPrice();
    });
  $('#select_slaughtered').click(function(){
        highlight_this_button(this, "slaughtered");
        loadSlaughtered();
    });


 function highlight_this_button(button, domain){
    //highlights only this button
    //sets 'selected_domain'
    $('#domain_selection a').css("background-color","yellow");
    $(button).css("background-color","#91FFA2");
    selected_domain = domain;
 }


    //PREPARE CANVAS
    d3.select(window).on("resize", throttle);

    var zoom = d3.behavior.zoom()
        .scaleExtent([1, 8])
        .on("zoom", move);

    var width = document.getElementById('map').offsetWidth-60;
    var height = width / 2;

    var topo,projection,path,svg,g;

    //DATA WHICH WILL BE LOADED LATER
    var data, livingAnimals, production, slaughter, consumption, population;

    var countries = {};

    var tooltip = d3.select("#map").append("div").attr("class", "tooltip hidden");
          //ofsets plus width/height of transform, plsu 20 px of padding, plus 20 extra for tooltip offset off mouse
      var offsetL = document.getElementById('map').offsetLeft+(width/2)+40;
      var offsetT = document.getElementById('map').offsetTop+(height/2)+20;


    setup(width,height);

    loadWorld();
    loadData();
    getDataFromDatabase("Germany");



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
          //gender : set_gender,

          success: function(data) {
            var json = JSON.parse(data);
            var c = json.country;
            average_dist = Math.round(json.average_dist);

            $('#portrait_country').html(c.toUpperCase());

            if(selected_domain=="production"){
              set_portrait("some number", "Some production sentence");
            }
            if(selected_domain=="trade"){
              set_portrait(average_dist  + " km", "A traded chicken has an average journey of");
            }
            if(selected_domain=="consumption"){
              set_portrait("some other number", "Some concumption sentence");
            }

            

            
          }             
        });  
        
    }
            function set_portrait(number, text){
              $('#portrait_text').html(text); 
              $('#portrait_data_distance').html(number);
              
            }

            function adjust_bar_value_and_width(this_id, text, value){
              $(this_id)
                .html(text + " " + value)
                .animate({width: value/700000 + "px"},500);
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
    function setup(width,height){
      projection = d3.geo.mercator()
        .translate([0, 0])
        .scale(width / 2 / Math.PI);

      path = d3.geo.path()
          .projection(projection);

      svg = d3.select("#map").append("svg")
          .attr("width", width)
          .attr("height", height)
          .append("g")
          .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")")
          .call(zoom);

      svg.append("rect")
        .attr({
          x:-width,
          y:-height,
          width: 2*width,
          height: 2*height,
        })
        .style("fill", "#BCE7FF");

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
          loadData();
        });
      });
    }

    /*
      LOADS THE DATA
    */
    function loadData(){

      //FINALLY LOAD DATA

      //DUMMY
      /*d3.csv("data/data.csv", function(error, _data){
        data = _data.filter(function(d,i){return i <20;});
        //updateBundledEdges();
      });*/

      //
      //d3.csv("data/livingAnimals.csv", function(error, _data){
      d3.csv("data/dataMeat.csv", function(error, _data){ 
        data = _data;//.filter(function(d,i){return i <20;});
        console.log("livingAnimals loaded");

        updateBundledEdges("Germany");
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
        return (d.Source == selectedCountry)// || d.Target == selectedCountry) 
                && (
                      (showCattle && d.Product == "Cattle") ||
                      (showChicken && d.Product == "Chickens") ||
                      (showPig && d.Product == "Pigs" ) ||
                      (d.Product == "Meat, chicken")
                    )
                ;
      })      
    }

    /* PRICING */

    var pricing;
    var maxPrice = -1;
    function loadPrice(){

     d3.csv("data/pricingChicken.csv", function(error, _data){
     // d3.csv("data/productionChicken.csv", function(error, _data){
        pricing = _data;//.filter(function(d,i){return i <20;});
        
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
      selectedCountry = country.properties.name;
      getDataFromDatabase(selectedCountry);
      updateBundledEdges();
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

      var _data = filterData();

      //re-calc maxImport/maxExport
      maxImport = -1;
      maxExport = -1;

      _data.forEach(function(d){
        if (d.Valuetype.toLowerCase().indexOf("import") != -1)
          maxImport = Math.max(d.Value, maxImport);
        else if (d.Valuetype.toLowerCase().indexOf("export") != -1)
          maxExport = Math.max(d.Value, maxExport);
      })
      console.log(maxImport + " " + maxExport);

      links = packageHierarchy(_data);

      g.selectAll(".trade")
        .data(links)
        .enter().append("path")
          .attr("class", function(d){
              if (d.data.Valuetype.toLowerCase().indexOf("import") != -1)
                return "link import";
              else if (d.data.Valuetype.toLowerCase().indexOf("export") != -1)
                return "link export";
              else
                return "link neitherImportNorExport";
          })
          .style({
            "stroke-width": function(d){ 
              if (d.data.Valuetype.toLowerCase().indexOf("import") != -1)
                return zoomLineWidth * Math.max(2, maxStrokeWidth * d.data.Value/maxImport);
              else if (d.data.Valuetype.toLowerCase().indexOf("export") != -1)
                return zoomLineWidth * Math.max(2, maxStrokeWidth*d.data.Value/maxExport);
              
            },
            fill: "none",
          })
          
          .attr("d", function(d){
            return line(d.points);
          })
          .on("mouseover", function(d){

            var mouse = d3.mouse(svg.node()).map( function(d) { return parseInt(d); } );
              tooltip
                .classed("hidden", false)
                .attr("style", "left:"+(mouse[0]+offsetL)+"px;top:"+(mouse[1]+offsetT)+"px")
                .html(d.data.Source + " --> " + d.data.Target + "\n"+ 
                      d.data.Product + " = " + d.data.Value + " " + d.data.Valuetype + ": "+
                      countries[d.data.Source].parent.name + " " + countries[d.data.Target].parent.name)
          })
          .on("mouseout",  function(d,i) {
            tooltip.classed("hidden", true);
          })

          .call(zoom);
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

          if (getDistance(countries[d.Source].coordinates, countries[d.Source].parent.coordinates) < 
              getDistance(countries[d.Source].coordinates, countries[d.Target].coordinates))
            ;//tmp.points.push(countries[d.Source].parent);

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
            console.log(tmp);                       
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

      return links;
    }

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
          //.style("fill", function(d, i) { return d.properties.color; });

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

    function move() {

      var t = d3.event.translate;
      var s = d3.event.scale;  
      var h = height / 3;
      
      t[0] = Math.min(width / 2 * (s - 1), Math.max(width / 2 * (1 - s), t[0]));
      t[1] = Math.min(height / 2 * (s - 1) + h * s, Math.max(height / 2 * (1 - s) - h * s, t[1]));

      zoom.translate(t);
      g.style("stroke-width", 1 / s).attr("transform", "translate(" + t + ")scale(" + s + ")");


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

    /*
        COMPARES COUNTRY lowercase AND WITHOUT spaces - _ or ,
    */
    /*
      topo.forEach(function(d){
        console.log("."+simplifyName(d.properties.name)+" {");
        console.log("\tfill: "+d.properties.color+",");
        console.log("}");
        console.log("");
      });
    */
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