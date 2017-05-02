<div class="col-md-1"></div>
<div class="col-md-4">   
    <div class="box box-info">
<?php echo  form_open_multipart(uri_string(),'role="form"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div>
         <?php echo form_hidden('post_data', '1');?>
       <div class="form-group">
           <p class="help-block">Upload file and import <br/> 
                       <?php echo anchor('../uploads/template/renewal_import.xlsx', 'Download import template', 'target="_blank"');?><br/>
                      </p>
                      <?php echo form_hidden('renewal', 1);?>
                      <label for="excelInputFile">File Input</label>
                      <?php echo form_upload('userfile');?>
       </div>
     </div>
        
    

       <div class="box-footer">
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right">Import</button>
      </div><!-- /.box-footer -->

<?php echo form_close();?>
      </div>
</div>
      <div class="col-md-7"></div>
