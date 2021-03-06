<?php if(!isset($db)) die(); ?>
<div class="container-fluid">
    <hr>

    <div class="row-fluid">
        <div class="span6">

            <?php include "model/alert-messages.php"; ?>

            <div class="widget-box">
                <div class="widget-title"> <span class="icon"> <i class="icon-plus"></i> </span>
                    <h5><?=ucfirst(ADD).' '.GROUP;?></h5>
                </div>
                <div class="widget-content nopadding">
                    <form action="#" method="POST" class="form-horizontal">

                        <div class="control-group">
                            <label class="control-label"><?=UP(GROUP).' '.NAME;?> :</label>
                            <div class="controls">
                                <input type="text" name="groupName" autocomplete="off" class="span11" placeholder="..." value="<?=(isset($_POST['groupName'])) ? htmlspecialchars($_POST['groupName']) : '';?>">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label"><?=UP(IMMUNITY);?>:</label>
                            <div class="controls">
                                <input type="number" min="0" name="immunity" autocomplete="off" class="span11" value="<?=(isset($_POST['immunity'])) ? htmlspecialchars($_POST['immunity']) : '0';?>">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label"><?=UP(USE1).' '.TIME;?></label>
                            <div class="controls">
                                <div class="input-prepend span4"> <span class="add-on"><?=DAYS[0];?></span>
                                    <input type="number" min="0" name="days" autocomplete="off" class="span5" value="<?=isset($_POST['days']) ? intval($_POST['days']) : 0;?>">
                                </div>
                                <div class="input-prepend span4"> <span class="add-on"><?=HOURS[0];?></span>
                                    <input type="number" min="0" name="hours" autocomplete="off" class="span5" value="<?=isset($_POST['hours']) ? intval($_POST['hours']) : 0;?>">
                                </div>
                                <div class="input-prepend span4"> <span class="add-on"><?=MINUTES[0];?></span>
                                    <input type="number" min="0" name="minutes" autocomplete="off" value="<?=isset($_POST['minutes']) ? intval($_POST['minutes']) : 0;?>" class="span5">
                                </div>
                                <br style="clear:both;">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label">Admin group?</label>
                            <div class="controls">
                                <label><input type="radio" value="1" <?php if(isset($_POST['radios']) && $_POST['radios'] == 1) echo "checked"; else if(!isset($_POST['radios'])) echo "checked"; ?> name="radios" />Yes</label>
                                <label><input type="radio" value="0" <?php if(isset($_POST['radios']) && $_POST['radios'] == 0) echo "checked";?> name="radios" />No</label>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label"><?=UP(FLAGS);?></label>
                            <div class="controls">
                                <select multiple name="flags[]">
                                    <?php
                                    foreach ((array) $GetAllFlags as $flag){
                                        //Check if active
                                        if(isset($_POST['flags'])) {
                                            if (!is_array($_POST['flags']))
                                                $class = (strpos($_POST['flags'], $flag['flag']) !== false) ? "selected" : "";
                                            else
                                                $class = (in_array($flag['flag'], $_POST['flags'])) ? "selected" : "";
                                        }
                                    ?>
                                    <option <?=$class;?>><?=$flag['flag'];?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="submit" class="btn btn-success pull-right"><?=(isset($match['params']['action'])) ? UP(EDIT) : UP(ADD);?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="span6">

            <div class="widget-box">
                <div class="widget-title"> <span class="icon"> <i class="icon-th"></i> </span>
                    <h5><?=UP(FLAGS);?></h5>
                </div>
                <div class="widget-content nopadding">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th><?=UP(FLAG);?></th>
                            <th><?=UP(NAME);?></th>
                            <th><?=UP(FLAG);?></th>
                            <th><?=UP(NAME);?></th>
                            <th><?=UP(FLAG);?></th>
                            <th><?=UP(NAME);?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $i = 0; $custom = 0;
                            foreach ((array) $GetAllFlags as $flag){

                                if(strpos($flag['name'], "custom") !== FALSE) {
                                    $custom++;
                                    $name = UP(constant(strtoupper($flag['name']))).' '.$custom;
                                } else {
                                    $name = UP(constant(strtoupper($flag['name'])));
                                }

                                $i++;

                                //Table
                                if($i == 4)
                                    echo '<tr>';
                                    echo '<td class="centerrow cflag" >'.$flag['flag'].'</td>';
                                    echo '<td>'.$name.'</td>';
                                if($i == 3)
                                    echo '</tr>';
                                //Table ends

                                if($i == 3)
                                    $i = 0;
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>


    </div>



    <div class="row-fluid">
        <div class="span12">

            <hr>

            <div class="widget-box">
                <div class="widget-title"> <span class="icon"> <i class="icon-th"></i> </span>
                    <h5><?=UP(ALL).' '.GROUP;?></h5>
                </div>
                <div class="widget-content nopadding">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th><?=UP(GROUP).' '.NAME;?></th>
                            <th><?=UP(FLAGS);?></th>
                            <th><?=UP(IMMUNITY);?></th>
                            <th><?=UP(USE1).' '.TIME;?></th>
                            <th><?=UP(EDIT);?></th>
                            <th><?=UP(DELETE);?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php foreach ((array)$GetAllGroups as $group) { if(!empty($group['groupName'])) {?>
                            <tr>
                                <td><?=$group['groupName'];?></td>
                                <td class="centerrow"><b><?=$group['flags'];?></b></td>
                                <td class="centerrow"><?=$group['immunity'];?></td>
                                <td class="centerrow"><?=convertToHoursMinsBans(intval($group['usetime']));?></td>
                                <td class="centerrow"><a href="<?=$CurrentURL;?>edit/<?=intval($group['gid']);?>" class="btn btn-warning btn-mini"><?=EDIT;?></a></td>
                                <td class="centerrow"><a href="#deleteAlert" onclick="updatedeleteurl(<?=$group['gid'];?>)" data-toggle="modal" class="btn btn-danger btn-mini"><?=DELETE;?></a></td>
                            </tr>
                            <?php } else {echo "<tr><td>-</td></tr>";} } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


</div>

<div id="deleteAlert" class="modal hide">
    <div class="modal-header">
        <button data-dismiss="modal" class="close" type="button">×</button>
        <h3><?=UP(DELETECONFIRM);?></h3>
    </div>
    <div class="modal-body">
        <p><?=UP(DELETECONFIRMTXT);?></p>
    </div>
    <div class="modal-footer">
        <a id="deleteurl" class="btn btn-primary" href="#"><?=UP(CONFIRM);?></a>
        <a data-dismiss="modal" class="btn" href="#"><?=UP(CANCEL);?></a>
    </div>
</div>

<script>
    function updatedeleteurl(id)
    {
        $('#deleteurl').attr('href', '<?=$CurrentURL;?>delete/' + id);
    }
</script>