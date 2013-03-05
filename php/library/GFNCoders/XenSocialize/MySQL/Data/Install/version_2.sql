-- SQL Data for GoodForNothing™ XenSocialize for XenForo --

--
-- Table structure for table `gfn_xensocialize_forum`
--

CREATE TABLE IF NOT EXISTS `gfn_xensocialize_forum` (
  `node_id` int(10) NOT NULL,
  `xensocialize_data` blob NOT NULL,
  `facebook` blob NOT NULL,
  `twitter` blob NOT NULL,
  PRIMARY KEY (`node_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gfn_xensocialize_user_network`
--

CREATE TABLE IF NOT EXISTS `gfn_xensocialize_user_network` (
  `user_id` int(10) NOT NULL,
  `facebook` blob NOT NULL,
  `twitter` blob NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gfn_xensocialize_user_option`
--

CREATE TABLE IF NOT EXISTS `gfn_xensocialize_user_option` (
  `user_id` int(10) NOT NULL,
  `post_on_network` tinyint(2) NOT NULL,
  `post_on_network_on_reply` tinyint(2) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------