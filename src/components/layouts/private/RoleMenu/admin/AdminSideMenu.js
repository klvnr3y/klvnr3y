import {
	faChartPie,
	faHome,
	faUsdCircle,
	faChartMixed,
	faBooks,
	faBell,
	faPaperPlane,
	faCogs,
	faBullhorn,
	faUsers,
	faFileCertificate,
	faTicket,
	faBook,
	faEye,
} from "@fortawesome/pro-light-svg-icons";
import { faCommentDots } from "@fortawesome/pro-solid-svg-icons";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

const AdminSideMenu = [
	{
		title: "Dashboard",
		path: "/dashboard",
		icon: <FontAwesomeIcon icon={faHome} />,
	},
	{
		title: "Subscribers",
		path: "/subscribers",
		icon: <FontAwesomeIcon icon={faUsers} />,
		children: [
			{
				title: "Current",
				path: "/subscribers/current",
			},
			{
				title: "Deactivated",
				path: "/subscribers/deactivated",
			},
		],
	},
	{
		title: "Notifications",
		path: "/notifications",
		icon: <FontAwesomeIcon icon={faBell} />,
	},
	{
		title: "Email Templates",
		path: "/email-templates",
		icon: <FontAwesomeIcon icon={faPaperPlane} />,
	},
	{
		title: "Account Type",
		path: "/account-type",
		icon: <FontAwesomeIcon icon={faCogs} />,
		children: [
			{
				title: "Caregivers",
				path: "/account-type/caregivers",
			},
			{
				title: "Care Professional",
				path: "/account-type/careprofessional",
			},
		],
	},
	{
		title: "References",
		path: "/references",
		icon: <FontAwesomeIcon icon={faBook} />,
		children: [
			// {
			//   title: "Question Category",
			//   path: "/references/question-category",
			// },
			// {
			//   title: "Advertisement Type",
			//   path: "/references/advertisement-type",
			// },
		],
	},
	{
		title: "Ticketing",
		path: "/ticketing",
		icon: <FontAwesomeIcon icon={faTicket} />,
	},
	{
		title: "Messages",
		path: "/message",
		icon: <FontAwesomeIcon icon={faCommentDots} />,
	},
	{
		title: "View As",
		path: "/viewas",
		icon: <FontAwesomeIcon icon={faEye} />,
	},
];

export default AdminSideMenu;
