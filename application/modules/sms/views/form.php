<div class="col-md-1"></div>
<div class="col-md-6">
 <div class="box box-info">
<?php echo form_open(uri_string(),'class="form-horizontal"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div>
      <div class="form-group">
          <label  class="col-sm-4 control-label">Mobile No: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($phone);?></div>
      </div>

     <div class="form-group">
          <label  class="col-sm-4 control-label">Message: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_textarea($ujumbe);?></div>
      </div>


     </div>


       <div class="box-footer">
           <button type="reset" class="btn btn-default">Clear</button>
         <button type="submit" class="btn btn-info pull-right"> Send</button>
      </div><!-- /.box-footer -->
<?php echo form_close();?>
</div>
</div>
      <div class="col-md-5"></div>
