<h4>Run in batch mode</h4>
<p>Run the model for every row of a csv file containing input data. No data is sent to esyN servers. Columns can be in any order and any additional columns added to the template will be ignored. </p>
<p>Upload your batch file and click Run. A summary of the results will be shown below. You can download the results table by clicking download, or explore the results under the "Run" tab by clicking "Send to dataset".</p>
<p><button class="btn btn-sm btn-primary" onclick="exportDecisionTreeProps()">Click</button> to download a template to fill in and upload.</p>

Select a file:<input type="file" id="file_upload_batch_run">
<output id="file_list_batch_run"></output>
<button type="button" id="file-upload-btn-batch" class="btn btn-primary" onclick="uploadBatchRun()">Run</button>
<button class="btn btn-warning" onclick="clearBatchRunResults()">Clear</button>
<button class="btn btn-info" onclick="downloadBatchRunResults(false)">Download</button>
<button class="btn btn-info" onclick="downloadBatchRunResults(true)">Download with calculator output</button>
<button class="btn btn-info" onclick="explore_tracking_dataset()">Send to dataset</button>
<button class="btn btn-info" onclick="show_batch_coverage()">Show coverage</button>
<div id='dt-model-result-batch'></div>


<h4>Leaf nodes</h4><p>Leaf nodes have no outgoing edges. These are the intended end points (e.g. final diagnosis of a patient) when the model runs. Highlight all leaf nodes to see where they lie in your model.</p>
<button onclick="highlight_leaf_nodes()" class="btn btn-info">Highlight leaf nodes</button>
<button onclick="remove_highlight_all()" class="btn btn-warning">Clear all highlights</button>
