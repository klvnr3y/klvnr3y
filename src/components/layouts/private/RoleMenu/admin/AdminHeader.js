import { Link } from "react-router-dom";
import { Menu, Typography } from "antd";

import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
	faUsers,
	faChartPie,
	faChartMixed,
} from "@fortawesome/pro-light-svg-icons";

export const menuLeft = (
	<>
		<div className="ant-menu-left-icon">
			<Link to="/subscribers/current">
				<span className="anticon">
					<FontAwesomeIcon icon={faUsers} />
				</span>
				<Typography.Text>Subscribers</Typography.Text>
			</Link>
		</div>

		<div className="ant-menu-left-icon">
			<Link to="/stats">
				<span className="anticon">
					<FontAwesomeIcon icon={faChartPie} />
				</span>
				<Typography.Text>Stats</Typography.Text>
			</Link>
		</div>
	</>
);

export const dropDownMenuLeft = () => {
	const items = [
		{
			key: "/subscribers/current",
			icon: <FontAwesomeIcon icon={faUsers} />,
			label: <Link to="/subscribers/current">Subscribers</Link>,
		},
	];

	return <Menu items={items} />;
};
