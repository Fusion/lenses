                	<form action="" class="jNice">
				<h3>Members</h3>
				<table cellpadding="0" cellspacing="0">
                                        <?php
                                                $bg = false;
                                                foreach($members as $member)
                                                {
                                                        $bg = !$bg;
                                                        if($bg) print "<tr>\n";
                                                        else    print "<tr class=\"odd\">\n";
                                                        print <<<EOB
                                                <td width="32">{$member->id}</td>
                                                <td>{$member->username}</td>
                                        </tr>

EOB;
                                                }
                                        ?>

				</table>
				<div>
					&nbsp;
				</div>
                    </form>
