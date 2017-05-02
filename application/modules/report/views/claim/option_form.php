<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php //echo $message;?>
            </div> 

            <div class="form-group">
                <label for="inputDaterange" class="col-sm-4 control-label">Reported: </label>
                <div class="col-sm-8">
                    <?php echo form_input($date_range); ?></div>
            </div>    

            <div class="form-group">
                <label for="inputClaimtype" class="col-sm-4 control-label"> Type: </label>
                <div class="col-sm-8">
                    <select name="claim_type" class="form-control select2" style="width: 44%;">    
                        <option value="">Select Type</option>
                        <?php
                        foreach ($claim_type_list as $claim_type) {
                            echo '<option value="' . $claim_type['id_claim_type'] . '" >' . $claim_type['type'] . '</option>';
                        }
                        ?>    

                    </select>   

                </div>
            </div>   

            <div class="form-group">
                <label for="inputRemark" class="col-sm-4 control-label"> Remark: </label>
                <div class="col-sm-8">
                    <select name="remark" class="form-control select2" style="width: 44%;">  
                        <option value="">Select Remark</option>
                        <?php
                        echo '<option value="1" >Closed</option>';
                        echo '<option value="2">Open</option>';
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
