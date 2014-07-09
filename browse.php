  <?php include("header-styles.php"); ?>
  <!-- header-styles does not include a closing head tag for compatibility with the builder page -->
  <script src ="analytics.js"></script>
  <meta name="google-site-verification" content="wq-MzHE_599sKpmAIAZkURalSvRGOIzhDnC81xb6mtY" />
  <meta name="keywords" content="Public Models, Alzheimer's Disease, ALS, AD, PD, Ageing">
  <!-- // <script src="src/jquery-2.1.0.min.js"></script> -->
    <script src="src/bootstrap.min.js"></script>
      <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">



    <!-- Custom styles for this template -->
    <!-- <link href="css/main.css" rel="stylesheet"> -->
    <link href="css/myesyn.css" rel="stylesheet">

    <script src ="analytics.js"></script>
    <meta name="google-site-verification" content="wq-MzHE_599sKpmAIAZkURalSvRGOIzhDnC81xb6mtY" />
    <script src="src/angular.min.js"></script>
    <script src="src/controllers4.js"></script>

  </head>
  <body id="explore" ng-controller="ProjectListCtrl"> <!-- the body id determines what gets highlighted in the menu bar -->
    <!-- header-menu will insert the menu bar, logo and links -->

    <?php include("header-menu.php"); ?>

    <div id="content" >

    <h1>Public models</h1>
    <p>Browse models that have been made public. If you have a My esyN account you can easily import these models into your workspace.</p>
    <p>
          Public models can be searched by name, type, tag, description and author.
   
          
          Enter search term: <input ng-model="query">
          </p>
  
   <div>
     <table class="table table-hover table-boredered table-striped">
          <tr><th><a href="" ng-click="predicate = 'label'; reverse = predicate == 'label' && !reverse">Project Name</a></th>
          <th></th><th>Type</th>
          <th><a href="" ng-click="predicate = 'ownername'; reverse = predicate == 'ownername' && !reverse">Author</a></th>
          <th>Tags</th>
          <!-- Uncomment the line below to enable view count (and also line 55) -->
          <!-- <th><a href="" ng-click="predicate = 'views'; reverse = predicate == 'views' && !reverse">Views</a></th> -->
          <th><a href="" ng-click="predicate = 'publishdate'; reverse = predicate == 'publishdate' && !reverse">Date Published</a></th></tr>
            <tr ng-repeat="project in projects | orderBy:predicate:reverse | filter:query">
              <td><a ng-href="http://www.esyn.org/builder.php?publishedid={{project.publishedid}}&type={{project.linktype}}">{{project.label}}</a></td>
              <td><button class="btn btn-success btn-xs" data-toggle="modal" data-target="#descriptionModal" <a ng-click="setSelection(project)">Show description</button></td>
              <td><span style="white-space:nowrap;">{{project.type}}</span></td>
              <td><span style="white-space:nowrap;">{{project.ownername}}</span></td>
              <td><ul class="taglist"><li ng-repeat="el in project.tags">{{el}}</li></ul></td>
              <!-- uncomment this line to enable view count (and also line 46) -->
              <!-- <td>{{project.views}}</td> -->
              <td>{{project.publishdate | date:'dd/MM/yy'}}</td>
            </tr>
            
            </table>
   </div>
      

    <!-- description modal -->
<div class="modal fade" id="descriptionModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Description of {{selecteditem.label}}</h4>
      </div>
      <div class="modal-body" id="publishForm">

        <!-- Description -->
        <div id="description"><p><b>Description</b></p><p>{{selecteditem.description}}</p></div>
        <div id="authorname"><p><b>Created by</b></p><p> {{selecteditem.ownername}}</p>  </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> 
</div> <!-- End content div  -->



    <script type="text/javascript">
    function showDescription(pid){
      //clear any existing text
      $('#description').text('Getting description...');
      $.ajax({
    type: "POST",
    url: "manager.php",
    data: { action: "getPublishedDescription", publishedid: pid}
    })
      .done(function( msg ) {
      console.log("message", msg);
      if(msg.description == ""){
        $('#description').text('No description of this project was provided.')
      } else {
        $('#description').text(msg.description)
      }

    });
    }
    </script>
  </body>
</html>