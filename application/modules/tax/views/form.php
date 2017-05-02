<div class="col-md-1"></div>
<div class="col-md-6">
 <div class="box box-info">      
<?php echo form_open(uri_string(),'class="form-horizontal"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div> 
         <div class="form-group">
             <label for="inputStatus" class="col-sm-4 control-label"> Type: <span class="text-red">*</span></label>
             <div class="col-sm-8">
             <select name="type" class="form-control select2" style="width: 43%;">    
                 <option value="">Select Type</option>
                      <?php   
foreach ($tax_type_list as $key => $value) {
       $selected=(isset($type) && $type==$key) ? 'selected="selected"' :'';
     	echo '<option value="'.$key.'" '. $selected .'>'.$value.'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>  
         
      <div class="form-group">
          <label for="inputPercentage" class="col-sm-4 control-label">Percentage: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($percentage);?></div>
      </div>     
         
<?php 
if(isset($id_tax)){ ?>

    <div class="form-group">
             <label for="inputStatus" class="col-sm-4 control-label"> Status: </label>
             <div class="col-sm-8">
             <select name="status" class="form-control select2" style="width: 43%;">    
                      <?php   
foreach ($status_type as $key => $value) {
       $selected=(isset($status) && $status==$key) ? 'selected="selected"' :'';
     	echo '<option value="'.$key.'" '. $selected .'>'.$value.'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>
<?php } ?>      
         
     </div>
    
    
       <div class="box-footer">
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_tax) ? 'Save' : 'Add';?></button>
      </div><!-- /.box-footer -->

<?php echo form_close();?>
</div>
</div>
      <div class="col-md-5"></div>