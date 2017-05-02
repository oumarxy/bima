<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">     
        <!--report/print_quarter_return_excel--> 
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div>  
            
              <div class="form-group">
                <label for="inputDaterange" class="col-sm-4 control-label">Year: </label>
                <div class="col-sm-8"><?php echo form_input($year); ?></div>
            </div> 
            
                     <div class="form-group">
                <label  class="col-sm-4 control-label"> Quarter: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="quarter" class="form-control select2" style="width: 50%;">   
                             <option value="">Select Quarter</option>
                        <option value="1" <?php echo (isset($quarter) && $quarter == 1) ? 'selected="selected"' : ''; ?>>January - March </option>
                        <option value="2" <?php echo (isset($quarter) && $quarter == 2) ? 'selected="selected"' : ''; ?>>April - June </option>
                        <option value="3" <?php echo (isset($quarter) && $quarter == 3) ? 'selected="selected"' : ''; ?>>July - September </option>
                        <option value="4"<?php echo (isset($quarter) && $quarter == 4) ? 'selected="selected"' : ''; ?>> October - December </option>
                    </select>   

                </div>
            </div>  

            <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Insurer: </label>
                <div class="col-sm-8">
                    <select name="insurer" class="form-control select2" style="width: 70%;">   
                        <option value="">Select Insurer</option>
                        <?php
                        foreach ($insurer_list as $insurer_) {
                            $selected = (isset($insurer) && $insurer == $insurer_['id_insurer']) ? 'selected="selected"' : '';
                            echo '<option value="' . $insurer_['id_insurer'] . '"' . $selected . ' >' . $insurer_['name'] . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div>  

            
        </div>


        <div class="box-footer text-center">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right">Print Excel</button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>