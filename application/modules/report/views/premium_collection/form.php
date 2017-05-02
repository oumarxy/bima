<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php //echo $message; ?>
            </div> 
                        
     <div class="form-group">
          <label for="inputDaterange" class="col-sm-4 control-label">Issued: </label>
          <div class="col-sm-8"><?php echo form_input($date_range);?></div>
      </div>    
  

            <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Insurer: </label>
                <div class="col-sm-8">
                    <select name="insurer" class="form-control select2" style="width: 55%;">   
                        <option value="">Select Insurer</option>
                        <?php
                        foreach ($insurer_list as $insurer) {
                            echo '<option value="' . $insurer['id_insurer'] . '" >' . $insurer['name'] . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div>      






        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right">Load</button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
