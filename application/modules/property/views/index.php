

<div class="col-xs-12">

    <div class="box">
        <div class="box-header">
            <h6 class="box-title text-green" ><?php echo $message; ?></h6>
        </div><!-- /.box-header -->
        <div class="box-body">
            <table id="example1" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Owner</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Value</th>
                        <th>Claim</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $property): ?>          
                        <tr>
                            <td><?php echo anchor(site_url('customer/view/' . $property->id_customer), customer_name_by_id($property->id_customer), 'title="View owner"'); ?></td>
                            <td><?php echo htmlspecialchars($property->description, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo Modules::run('property_type/get_where', $property->id_property_type)->row()->type; ?></td>
                            <td><?php echo htmlspecialchars($property->number, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($property->property_value, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo (!$property->claim) ? '<span class="label label-success">' . claim_status_by_id($property->claim) . '</span>' : '<span class="label label-danger">' . claim_status_by_id($property->claim) . '</span>'; ?></td>
                            <td> <?php echo anchor("property/edit/" . $property->id_property, '<i class="fa fa-edit"></i>', 'title="Edit"'); ?>| <?php echo anchor("property/delete/" . $property->id_property, '<i class="fa fa-trash"></i>', array('title' => "Delete", 'onclick' => "return confirm('This will delete insurance and payment records associated to this property. Do you want to delete?')")); ?> | <?php $insurance_type = ($property->id_property_type == 1) ? 1 : 2;
                   echo anchor("insurance/register/" . $insurance_type . '/' . $property->id_property, '<i class="fa fa-umbrella"></i>', 'title="Insure Property"'); ?>
                            </td>
                        </tr>

<?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                    <tr>
                        <th>Owner</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Value</th>
                        <th>Claim</th>
                        <th>Options</th>
                    </tr>
                </tfoot>
            </table>
            <p><?php echo isset($id_customer) ? anchor('property/register/' . $id_customer, '<i class="fa fa-plus-circle"></i> Register property') : anchor('property/register', '<i class="fa fa-plus-circle"></i> Register property') ?> </p>

        </div><!-- /.box-body -->
    </div><!-- /.box -->



</div>
