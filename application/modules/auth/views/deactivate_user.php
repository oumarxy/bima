<div class="col-md-1"></div> 
<div class="col-md-3">
<div class="box box-info"> 
<?php echo form_open(uri_string(),'class="form-horizontal"');?>

<div class="box-body">

      <div class="form-group">
                    <label class="col-sm-4 control-label">
                      <input type="radio"name="confirm" value="yes" class="flat-red" checked>
                      <?php  echo lang('deactivate_confirm_y_label'); ?>
                    </label>
                    <label class="col-sm-4 control-label">
                      <input type="radio" name="confirm" value="no"  class="flat-red">
                       <?php echo lang('deactivate_confirm_n_label');?>
                    </label>
                  </div>
    

  <?php echo form_hidden($csrf); ?>
  <?php echo form_hidden(array('id'=>$user->id)); ?>
</div>
    
     <div class="box-footer">
         <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo  lang('deactivate_submit_btn');?></button>
      </div><!-- /.box-footer -->   

<?php echo form_close();?>
</div>
</div>
 <div class="col-md-8"></div>   
