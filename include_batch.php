<h4>All nodes</h4>
<p>Standard actions to apply to all nodes in the current network.</p>
<button type="button" class="btn btn-primary" onclick="remove_highlight_all()">Remove highlight</button>

<h4>Node list</h4>
<p>Select an option below to apply to a list of nodes. The node list should be a text file with one name per line.</p>
<div class="radio">
  <label><input type="radio" name="batchradio" value="add_highlight">Add highlight</label>
</div>
<div class="radio">
  <label><input type="radio" name="batchradio" value="remove_highlight">Remove highlight</label>
</div>

Select a file:<input type="file" id="file_upload_batch">
<output id="file_list"></output>


<button type="button" id="file-upload-btn" class="btn btn-primary" onclick="batchListUpload()">Process</button>

