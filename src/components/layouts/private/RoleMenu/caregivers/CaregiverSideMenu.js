import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import {
	faChartMixed,
	faHome,
	faBooks,
	faCogs,
	faChartPieAlt,
	faCommentDots,
} from "@fortawesome/pro-regular-svg-icons";

const CaregiversSideMenu = [
	{
		title: "Dashboard",
		path: "/dashboard",
		icon: <FontAwesomeIcon icon={faHome} />,
	},
	{
		title: "Support",
		path: "/support",
		icon: <FontAwesomeIcon icon={faCogs} />,
		children: [
			{
				title: "FAQs",
				path: "/support/faqs",
			},
			{
				title: "Ticketing",
				path: "/support/ticketing",
			},
		],
	},
	{
		title: "Messages",
		path: "/message",
		icon: <FontAwesomeIcon icon={faCommentDots} />,
	},
];

export default CaregiversSideMenu;
