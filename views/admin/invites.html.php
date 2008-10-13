                	<form action="" class="jNice">
				<h3>Invites</h3>
				<table cellpadding="0" cellspacing="0">
                                        <?php
                                                $bg = false;
                                                foreach($invites as $invite)
						{
							$bg = !$bg;
							if($bg) print "<tr>\n";
							else    print "<tr class=\"odd\">\n";
							print <<<EOB
						<td width="32">{$invite->id}</td>

EOB;
							if($invite->member)
								print <<<EOB
						<td>{$invite->member->username}</td>
						<td>{$invite->member->email}</td>
						</tr>

EOB;
							else
								print <<<EOB
						<td></td>
						<td></td>
						</tr>

EOB;
						}
                                        ?>

				</table>
				<div>
					&nbsp;
				</div>
                    </form>
