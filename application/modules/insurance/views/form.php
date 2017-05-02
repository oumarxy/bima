<div class="col-md-1"></div>
<div class="col-md-6">
    <div class="box box-info">      
        <?php echo form_open(uri_string(), 'class="form-horizontal"'); ?>

        <div class="box-body">
            <div class="form-group has-error text-center text-red">
                <?php echo $message; ?>
            </div> 
            <div class="form-group">
                <label for="inputIssueddate" class="col-sm-4 control-label">Date Issued: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($issued_on); ?></div>
            </div>

            <div class="form-group">
                <label for="inputCommdate" class="col-sm-4 control-label">Date Comm: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($comm_on); ?></div>
            </div>  
           
                <div class="form-group">
                    <label for="inpuDuration" class="col-sm-4 control-label"> Duration: <span class="text-red">*</span></label>
                    <div class="col-sm-8">
                        <select name="duration" class="form-control select2" style="width: 55%;">  
                            <option value="">Select Duration</option>
                            <?php
                            foreach ($duration_list as $key => $value) :
                                $selected = (isset($duration) && $duration == $key) ? 'selected="selected"' : '';
                                echo '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
                            endforeach;
                            ?>    

                        </select>   

                    </div>
                </div>   
          

            <div class="form-group">
                <label for="inputCovernote" class="col-sm-4 control-label">Cover Note: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($cover_note); ?></div>
            </div>

            <div class="form-group">
                <label for="inputCovertype" class="col-sm-4 control-label"> Cover Type: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="id_cover_type" class="form-control select2" style="width: 55%;">    
                        <?php
                        foreach ($cover_type_list as $cover_type) :
                            $selected = (isset($id_cover_type) && $id_cover_type == $cover_type['id_cover_type']) ? 'selected="selected"' : '';
                            echo '<option value="' . $cover_type['id_cover_type'] . '" ' . $selected . '>' . $cover_type['type'] . '</option>';
                        endforeach;
                        ?>    

                    </select>   

                </div>
            </div>  

            <?php if ($type == 1): ?>
                <div class="form-group">
                    <label for="inputStickernumber" class="col-sm-4 control-label">Sticker Number: <span class="text-red">*</span></label>
                    <div class="col-sm-8"><?php echo form_input($sticker_number); ?></div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="inputPolicynumber" class="col-sm-4 control-label">Policy Number: <span class="text-red"></span></label>
                <div class="col-sm-8"><?php echo form_input($policy_number); ?></div>
            </div>

            <div class="form-group" title="Value without Vat">
                <label for="inputPremium" class="col-sm-4 control-label">Premium Amount: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($premium); ?></div>
            </div>
            <?php if (!isset($id_insurance) || (trim($this->uri->segment(2)) == 'renew')): ?>
                <div class="form-group" title="Total amount paid">
                    <label for="inputAmount" class="col-sm-4 control-label">Paid Amount: </label>
                    <div class="col-sm-8"><?php echo form_input($amount); ?></div>
                </div>    

                <div class="form-group">
                    <label for="inputReceipt" class="col-sm-4 control-label">Receipt: </label>
                    <div class="col-sm-8"><?php echo form_input($receipt); ?></div>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label for="inputInsurer" class="col-sm-4 control-label"> Insurer: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="id_insurer" class="form-control select2" style="width: 55%;">    
                        <?php
                        foreach ($insurer_list as $insurer) :
                            $selected = (isset($id_insurer) && $id_insurer == $insurer['id_insurer']) ? 'selected="selected"' : '';
                            echo '<option value="' . $insurer['id_insurer'] . '" ' . $selected . '>' . $insurer['name'] . '</option>';
                        endforeach;
                        ?>    

                    </select>   

                </div>
            </div>

            <div class="form-group">
                <label for="inputVat" class="col-sm-4 control-label"> Vat: <span class="text-red">*</span></label>
                <div class="col-sm-8">
                    <select name="vat" class="form-control select2" style="width: 55%;">   
                        <option>Select Vat</option>
                        <?php
                        foreach ($tax_list as $tax) :
                            $selected = (isset($vat) && $vat == $tax['percentage']) ? 'selected="selected"' : '';
                            echo '<option value="' . $tax['percentage'] . '" ' . $selected . '>' . $tax['percentage'] . ' %</option>';
                        endforeach;
                        ?>    

                    </select>   

                </div>
            </div> 


            <div class="form-group">
                <label for="inputCommission" class="col-sm-4 control-label">Commission: <span class="text-red">*</span></label>
                <div class="col-sm-8"><?php echo form_input($commission); ?> %</div>
            </div>          

        </div>


        <div class="box-footer">
            <button type="button" class="btn btn-default" onclick="history.go(-1);">Cancel</button>
            <button type="submit" class="btn btn-info pull-right"><?php echo isset($id_insurance) ? 'Save' : 'Register'; ?></button>
        </div><!-- /.box-footer -->
        <?php echo form_close(); ?>
    </div>
</div>
<div class="col-md-5"></div>
