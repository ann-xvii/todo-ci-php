CREATE TABLE tasks (
	task_id int(11) NOT NULL AUTO_INCREMENT,
	task_desc varchar(255) NOT NULL, 
	task_due_date datetime DEFAULT NULL,
	task_created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	task_status enum('done','todo') NOT NULL,
PRIMARY KEY (task_id) 
) ENGINE = InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;