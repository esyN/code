<li class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown" href="#">Export<span class="caret"></span></a>
      <ul class="dropdown-menu">
        <li><button onclick="downloadSBML()" class="btn btn-link" id="export-sbml" >Export to SBML L3V1</button></li>
        <li><button onclick="megamerge()" class="btn btn-link" id="export-matrices" >Export matrices</button></li>
        <li><button onclick="exportToTC('all')" class="btn btn-link" id="tc-all" >Export project as csv</button></li>
        <li><button onclick="exportToTC('merge')" class="btn btn-link" id="tc-all" >Merge and export project as csv</button></li>
        <li><button onclick="exportToTC('current')" class="btn btn-link" id="tc-current" >Export current network as csv</button></li>
        <li><button onclick="exportCitations()" class="btn btn-link" id="tc-citations" >Export citations</button></li>
        <li><button onclick="toPNG('all')" class="btn btn-link" id="png-all" >Export whole network as PNG</button></li>
		<li><button onclick="toPNG('current')" class="btn btn-link" id="png-current" >Export current view as PNG</button></li>
      </ul>
    </li>