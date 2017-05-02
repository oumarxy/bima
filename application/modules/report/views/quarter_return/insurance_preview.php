

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" >  <?php //echo $message;             ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Issued Name</th>
                        <?php if ($this->input->post('insurer') == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                        <th>Cover Note</th>
                        <th>Sticker Number</th>
                        <th>Property Number</th>
                        <th>Premium Amount</th>
                        <th>Vat</th>
                        <th>Premium Paid</th>
                        <th>Out Bal</th>
                        <th>Expire</th>
                        <?php if ($this->input->post('remark') == '') { ?>
                            <th>Remark</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($insurances as $insurance): ?>   

                        <?php
                        $premium_paid_amount = Modules::run('payment/sum_where', array('id_insurance' => $insurance->id_insurance));
                        $out_bal_amount = $insurance->premium - Modules::run('payment/sum_where', array('id_insurance' => $insurance->id_insurance));
                        ?>
                        <tr>
                            <td><?php echo anchor(site_url('customer/view/' . customer_id_by_id_property($insurance->id_property)), customer_by_id_property($insurance->id_property), 'title="View owner"'); ?></td>
                            <?php if ($this->input->post('insurer') == '') { ?>
                                <td><?php echo insurer_by_id($insurance->id_insurer); ?></td>
                            <?php } ?>
                            <td><?php echo $insurance->cover_note; ?></td>
                            <td><?php echo $insurance->sticker_number; ?></td>
                            <td><?php echo anchor(site_url('property/view/' . $insurance->id_property), property_number_by_id($insurance->id_property), 'View Property'); ?></td>
                            <td><?php echo number_format($insurance->premium,2); ?></td>
                            <td><?php echo number_format($insurance->tax,2); ?></td>
                            <td><?php echo number_format($premium_paid_amount,2); ?></td>
                            <td><?php echo number_format($out_bal_amount,2); ?></td>
                            <td><?php echo format_date('', $insurance->expired_on); ?></td>
                            <?php if ($this->input->post('remark') == '') { ?>
                                <td><?php echo ($insurance->paid) ? '<span class="label label-success">' . paid_status_by_id($insurance->paid) . '</span>' : '<span class="label label-danger">' . paid_status_by_id($insurance->paid) . '</span>'; ?></td>
                            <?php } ?>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Issued Name</th>
                        <?php if ($this->input->post('insurer') == '') { ?>
                            <th>Insurer</th>
                        <?php } ?>
                        <th>CoverNote</th>
                        <th>Sticker Number</th>
                        <th>Property Number</th>
                        <th>Premium Amount</th>
                        <th>Vat</th>
                        <th>Premium Paid</th>
                        <th>Out Bal</th>
                        <th>Expire</th>
                        <?php if ($this->input->post('remark') == '') { ?>
                            <th>Remark</th>
                        <?php } ?>
                    </tr>
                </tfoot>
            </table>
            <p>
                <?php echo form_open('report/print_insurance_pdf', 'style="display:inline;"'); ?>
                <input type="hidden" value="<?php echo $this->input->post('from_date'); ?>" name="from_date"/>
                <input type="hidden" value="<?php echo $this->input->post('to_date'); ?>" name="to_date"/>
                <input type="hidden" value="<?php echo $this->input->post('insurer'); ?>" name="insurer"/>
                <input type="hidden" value="<?php echo $this->input->post('remark'); ?>" name="remark"/>

                <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="pdf" value="Export to PDF"/>
                <?php echo form_close(); ?>

                &nbsp; &nbsp; &nbsp;

                <?php echo form_open('report/print_insurance_excel', 'style="display:inline;"'); ?>
                <input type="hidden" value="<?php echo $this->input->post('from_date'); ?>" name="from_date"/>
                <input type="hidden" value="<?php echo $this->input->post('to_date'); ?>" name="to_date"/>
                <input type="hidden" value="<?php echo $this->input->post('insurer'); ?>" name="insurer"/>
                <input type="hidden" value="<?php echo $this->input->post('remark'); ?>" name="remark"/>
                <input type="submit" style="border:1px solid #FFFFFF; background-color:#FFFFFF;cursor:pointer; color:blue;text-decoration:underline;" name="excel" value="Export to Excel"/>
                <?php echo form_close(); ?>

            </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>