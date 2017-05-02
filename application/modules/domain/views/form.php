<div class="col-md-1"></div>
<div class="col-md-6">
 <div class="box box-info">      
<?php echo form_open(uri_string(),'class="form-horizontal"');?>

     <div class="box-body">
          <div class="form-group has-error text-center text-red">
             <?php echo $message;?>
         </div> 
      <div class="form-group">
          <label for="inputName" class="col-sm-4 control-label">Name: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($name);?></div>
      </div>
       <div class="form-group">
          <label for="inputContactperson" class="col-sm-4 control-label">Contact Person: <span class="text-red">*</span></label>
          <div class="col-sm-8"><?php echo form_input($contact_person);?></div>
      </div>  
         
       <div class="form-group">
          <label for="inputAddress" class="col-sm-4 control-label">Address: </label>
          <div class="col-sm-8"><?php echo form_input($address);?></div>
      </div>   
        <div class="form-group">
          <label for="inputPhone" class="col-sm-4 control-label">Phone:  <span class="text-red">*</span></label>
          <div class="col-sm-8"> <input name="phone" type="text" value="<?php echo $phone;?>" data-inputmask='"mask": "9999-999-999"' data-mask placeholder=" 0714-607-310"></div>
      </div>
         
         <div class="form-group">
          <label for="inputEmail" class="col-sm-4 control-label">Email: </label>
          <div class="col-sm-8"><?php echo form_input($email);?></div>
      </div>    
                     
         
<?php 
if(isset($id_insurer)){?>

             <div class="form-group">
             <label for="inputStatus" class="col-sm-4 control-label"> Status: </label>
             <div class="col-sm-8">
             <select name="status" class="form-control select2" style="width: 44%;">    
                      <?php   
foreach ($status_type as $key => $value) {
       $selected=(isset($id_insurer) && $status==$key) ? 'selected="selected"' :'';
     	echo '<option value="'.$key.'" '. $selected .'>'.$value.'</option>';
     }
     ?>    
                     
                 </select>   
             
             </div>
         </div>
<?php }?>
     </div>
    

       <div class="box-footer">
           <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
         <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_insurer) ? 'Save' : 'Add';?></button>
      </div><!-- /.box-footer -->
<?php echo form_close();?>
</div>
</div>
      <div class="col-md-5"></div>
      