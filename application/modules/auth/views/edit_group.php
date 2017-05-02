<div class="col-md-1"></div> 
<div class="col-md-4">
 <div class="box box-info">     

<?php echo form_open(current_url(),'class="form-horizontal"');?>
    <div class="box-body">
         <div class="form-group has-error text-red text-center"><?php echo $message;?></div>   
       <div class="form-group">
           <label for="inputGroupname" class="col-sm-4 control-label"><?php echo lang('edit_group_name_label', 'group_name');?> </label>
           <div class="col-sm-8"><?php echo form_input($group_name);?></div>
       </div>

        <div class="form-group">
            <label for="inputGroupdescription" class="col-sm-4 control-label"><?php echo lang('edit_group_desc_label', 'description');?> </label>
            <div class="col-sm-8"> <?php echo form_input($group_description);?></div>
        </div>
    </div>
    
        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo  lang('edit_group_submit_btn');?></button>
      </div><!-- /.box-footer -->
<?php echo form_close();?>
 </div>     
</div>
<div class="col-md-7"></div>        