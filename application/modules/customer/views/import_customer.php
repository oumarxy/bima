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
                      <label for="excelInputFile">File Input</label>
                      <?php echo form_upload('userfile');?>
                      <p class="help-block">Upload file and import, <?php echo anchor('../uploads/template/customers_import.xlsx', 'Download template', 'target="_blank"');?></p>
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
