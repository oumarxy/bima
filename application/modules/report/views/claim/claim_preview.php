

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php //echo $message;         ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Reported Date</th>
                        <th>Particular</th>
                        <th>Property</th>
                        <?php if ($this->input->post('claim_type') == '') { ?>
                            <th>Nature</th>
                        <?php } ?>
                        <th>Insurer</th>
                        <th>Claim Amount</th>
                        <th>Settlement Amount</th>
                        <?php if ($remark == '') { ?>
                            <th>Remarks</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $claim): ?>
                        <?php
                        $insurance = Modules::run('insurance/get_where_custom', array('id_insurance' => $claim->id_insurance, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $insurer = Modules::run('insurer/get_where_custom', array('id_insurer' => $insurance->id_insurer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $property = Modules::run('property/get_where_custom', array('id_property' => $insurance->id_property, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                        $customer = Modules::run('customer/get_where_custom', array('id_customer' => $property->id_customer, 'id_domain' => $this->ion_auth->get_id_domain()))->row();
                         ?>

                        <tr>
                            <td><?php
                    echo format_date('', $claim->reported_on);
                        ?></td>
                            <td><?php echo $customer->name; ?></td>
                            <td><?php echo $property->number; ?></td>

                            <?php if ($this->input->post('claim_type') == '') { ?>
                                <td><?php echo Modules::run('claim_type/get_where_custom', array('id_claim_type' => $claim->id_claim_type, 'id_domain' => $this->ion_auth->get_id_domain()))->row()->type; ?></td>
                            <?php } ?>
                            <td><?php echo $insurer->name; ?></td>
                            <td><?php echo ($claim->claim_amount > 0) ? number_format($claim->claim_amount) : '-'; ?></td>
                            <td><?php echo ($claim->settlement_amount > 0) ? number_format($claim->settlement_amount) : '-'; ?></td>
                            <?php if ($remark == '') { ?>
                                <td><?php echo remark_by_id($claim->remark); ?> ,<?php echo ($claim->paid) ? ' Paid ' : ' Not paid ' ?></td>
                            <?php } ?>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>

                        <th>Reported Date</th>
                        <th>Particular</th>
                        <th>Property</th>
                        <?php if ($this->input->post('claim_type') == '') { ?>
                            <th>Nature</th>
                        <?php } ?>
                        <th>Insurer</th>
                        <th>Claim Amount</th>
                        <th>Settlement Amount</th>
                        <?php if ($remark == '') { ?>
                            <th>Remarks</th>
                        <?php } ?>
                    </tr>
                </tfoot>
            </table>
            <?php if (count($claims) > 0): ?>
                <p>
                    <?php echo form_open('report/print_claim_pdf', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $claim_type; ?>" name="claim_type"/>
                    <input type="hidden" value="<?php echo $remark; ?>" name="remark"/>

                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="pdf" value="Export to PDF"/>
                    <?php echo form_close(); ?>

                    &nbsp; &nbsp; &nbsp;

                    <?php echo form_open('report/print_claim_excel', 'style="display:inline;"'); ?>
                    <input type="hidden" value="<?php echo $from_date; ?>" name="from_date"/>
                    <input type="hidden" value="<?php echo $to_date; ?>" name="to_date"/>
                    <input type="hidden" value="<?php echo $claim_type; ?>" name="claim_type"/>
                    <input type="hidden" value="<?php echo $remark; ?>" name="remark"/>
                    <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="excel" value="Export to Excel"/>
                    <?php echo form_close(); ?>
                </p>
            <?php endif; ?>
        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
