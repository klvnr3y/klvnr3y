import { BrowserRouter as Router, Route, Switch } from "react-router-dom";
import { QueryClient, QueryClientProvider } from "react-query";
import { userData } from "../providers/companyInfo";
import {
	faTicket,
	faLightbulbOn,
	faUserEdit,
	faCreditCard,
	faFileInvoiceDollar,
	faCommentDots,
	faLock,
	faShieldKeyhole,
} from "@fortawesome/pro-solid-svg-icons";

import "antd/dist/antd.css";

/** sass */
import "../assets/css/ui/helper/helper.css";
import "../assets/css/ui/card/card.css";
import "../assets/css/ui/input/input.css";
import "../assets/css/ui/button/button.css";
import "../assets/css/ui/tooltip/tooltip.css";
import "../assets/css/ui/checkbox/checkbox.css";
import "../assets/css/ui/datepicker/datepicker.css";
import "../assets/css/ui/steps/steps.css";
import "../assets/css/ui/radio/radio.css";
import "../assets/css/ui/quill/quill.css";
import "../assets/css/ui/typography/typography.css";
import "../assets/css/ui/spinner/spinner.css";
import "../assets/css/ui/collapse/collapse.css";
import "../assets/css/ui/accordion/accordion.css";
import "../assets/css/ui/navigation/navigation.css";
import "../assets/css/ui/pagination/pagination.css";
import "../assets/css/ui/tabs/tabs.css";
import "../assets/css/ui/modal/modal.css";
import "../assets/css/ui/table/table.css";
import "../assets/css/ui/header/header.css";
import "../assets/css/ui/sidemenu/sidemenu.css";
import "../assets/css/ui/breadcrumb/breadcrumb.css";
import "../assets/css/ui/page_header/page_header.css";
import "../assets/css/ui/upload/upload.css";
import "../assets/css/ui/form/form.css";
import "../assets/css/ui/footer/footer.css";
import "../assets/css/ui/public-layout/public-layout.css";
import "../assets/css/ui/private-layout/private-layout.css";

import "../assets/css/main/main.css";

import "../assets/css/errors/maintenance/maintenance.css";

import "../assets/css/pages/login/login.css";
import "../assets/css/pages/create-password/create-password.css";
import "../assets/css/pages/register-layout/register-layout.css";
import "../assets/css/pages/dashboard/dashboard.css";
import "../assets/css/pages/profile/profile.css";
import "../assets/css/pages/payment-and-invoices/payment-and-invoices.css";
import "../assets/css/pages/messages/messages.css";
import "../assets/css/pages/profile-subscription/profile-subscription.css";

/** end sass */

/** errors */

import Error404 from "../views/errors/Error404";
import Error500 from "../views/errors/Error500";
// import PageMaintenance from "../views/errors/PageMaintenance";

/** end errors */

/** public views */

import PageLogin from "../views/public/PageLogin/PageLogin";
import PageCreatePassword from "../views/public/PageCreatePassword/PageCreatePassword";
import PageRegister from "../views/public/PageRegister/PageRegister";
import PageRegistrationSetPassword from "../views/public/PageRegister/PageRegistrationSetPassword";
import PageForgotPassword from "../views/public/ForgotPassword/PageForgotPassword";

/** end public views */

/** private views */

import RouteAdmin from "./RouteRole/RouteAdmin";
import RouteCaregivers from "./RouteRole/RouteCaregivers";
import RouteCareProfessional from "./RouteRole/RouteCareProfessional";

import PublicRoute from "./PublicRoute";
import PrivateRoute from "./PrivateRoute";

import PageTicketing from "../views/private/PageTicketing/PageTicketing";
import PageTicketingAdd from "../views/private/PageTicketing/PageTicketingAdd";
import PageTicketingView from "../views/private/PageTicketing/PageTicketingView";
import PageFaqs from "../views/private/PageFaqs/PageFaqs";
import PageProfile from "../views/private/PageProfile/PageProfile";
import PageChangeRenewSubscription from "../views/private/PageProfile/PageChangeRenewSubscription";
import PagePaymentAndInvoices from "../views/private/PageProfile/PagePaymentAndInvoices";
import PageUpgradeSubscription from "../views/private/PageProfile/PageUpgradeSubscription";
import PageMessage from "../views/private/PageMessage/PageMessage";
import PagePolicy from "../views/private/PagePolicy/PagePolicy";
import PageTermsAndConditions from "../views/private/PageTermsAndConditions/PageTermsAndConditions";
import PageCookie from "../views/private/PageCookie/PageCookie";

import Page2fa from "../views/private/Page2fa/Page2fa";

/** end private views */

// const token = localStorage.token;
const queryClient = new QueryClient();

// console.log("userData", userData());

export default function Routes() {
	return (
		<QueryClientProvider client={queryClient}>
			<Router>
				<Switch>
					{/* public route */}
					<PublicRoute exact path="/" component={PageLogin} title="Login" />
					<PublicRoute
						exact
						path="/create-password"
						component={PageCreatePassword}
						title="Create Password"
					/>

					<PublicRoute
						exact
						path="/register"
						component={PageRegister}
						title="Register"
					/>

					<PublicRoute
						exact
						path="/register/:token"
						component={PageRegister}
						title="Register"
					/>

					<PublicRoute
						exact
						path="/forgot-password/:token/:id"
						component={PageForgotPassword}
						title="Forgot Password"
					/>

					<PublicRoute
						exact
						path="/register/setup-password/:token"
						component={PageRegistrationSetPassword}
						title="Register - Setup Password"
					/>
					{/* end public route */}

					{/* private route */}

					{/* support/faqs */}
					<PrivateRoute
						exact
						path="/support/faqs"
						title="Questions"
						subtitle="FREQUENTLY ASKED"
						component={PageFaqs}
						pageHeaderIcon={faLightbulbOn}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Support",
								link: "/support/faqs",
							},
							{
								name: "FAQ'S",
								link: "/support/faqs",
							},
						]}
					/>
					{/* end support/faqs */}

					{/* ticketing */}
					<PrivateRoute
						exact
						path="/ticketing"
						title="Ticket"
						subtitle="CREATE A"
						component={PageTicketing}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Create a Ticket",
								link: "/ticketing/ticketing",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/support/ticketing"
						title="Ticket"
						subtitle="CREATE A"
						component={PageTicketing}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Create a Ticket",
								link: "/support/ticketing/ticketing",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/ticketing/create"
						title="Ticket"
						subtitle="CREATE A"
						component={PageTicketingAdd}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Create a Ticket",
								link: "/ticketing/create",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/support/ticketing/create"
						title="Ticket"
						subtitle="CREATE A"
						component={PageTicketingAdd}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Create a Ticket",
								link: "/support/ticketing/create",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/ticketing/reply"
						title="Ticket"
						subtitle="VIEW/REPLY TO"
						component={PageTicketingView}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "View/Reply to Ticket",
								link: "/ticketing/reply",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/support/ticketing/reply"
						title="Ticket"
						subtitle="VIEW/REPLY TO"
						component={PageTicketingView}
						pageHeaderIcon={faTicket}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "View/Reply to Ticket",
								link: "/support/ticketing/reply",
							},
						]}
					/>
					{/* end ticketing */}

					{/* profile/account */}
					<PrivateRoute
						exact
						path="/profile/account"
						title="Profile"
						subtitle="EDIT ACCOUNT"
						component={PageProfile}
						pageHeaderIcon={faUserEdit}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Edit Profile",
								link: "/profile/account",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/profile/account/subscription"
						title="Subscription"
						subtitle="CHANGE/RENEW"
						component={PageChangeRenewSubscription}
						pageHeaderIcon={faCreditCard}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Profile",
								link: "/profile/account",
							},
							{
								name: "Subscription",
								link: "/profile/account/subscription",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/profile/account/payment-and-invoices"
						title="Account"
						subtitle="INVOICES &"
						component={PagePaymentAndInvoices}
						pageHeaderIcon={faFileInvoiceDollar}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Invoices & Account",
								link: "/profile/account/payment-and-invoices",
							},
						]}
					/>
					<PrivateRoute
						exact
						path="/profile/account/subscription/upgrade-subscription"
						title="Subscription"
						subtitle="UPDATE"
						component={PageUpgradeSubscription}
						pageHeaderIcon={faCreditCard}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Update Subscription",
								link: "/profile/account/subscription/upgrade-subscription",
							},
						]}
					/>
					{/* end profile/account */}

					<PrivateRoute
						exact
						path="/message"
						title="Messages"
						subtitle="VIEW"
						component={PageMessage}
						pageHeaderIcon={faCommentDots}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Messages",
								link: "/message",
							},
						]}
					/>

					<PrivateRoute
						exact
						path="/policy"
						title="Policy"
						subtitle="PRIVACY"
						component={PagePolicy}
						pageHeaderIcon={faLock}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Policy",
								link: "/policy",
							},
						]}
					/>

					<PrivateRoute
						exact
						path="/terms-and-condition"
						title="Policy"
						subtitle="TERMS AND CONDITIONS"
						component={PageTermsAndConditions}
						pageHeaderIcon={faLock}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Terms & Condition",
								link: "/terms-and-condition",
							},
						]}
					/>

					<PrivateRoute
						exact
						path="/cookies"
						title="Policy"
						subtitle="COOKIE"
						component={PageCookie}
						pageHeaderIcon={faLock}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Cookies",
								link: "/cookies",
							},
						]}
					/>

					<PrivateRoute
						exact
						path="/profile/2fa"
						title="Authentication"
						subtitle="2FA"
						component={Page2fa}
						pageHeaderIcon={faShieldKeyhole}
						breadcrumb={[
							{
								name: "Dashboard",
								link: "/dashboard",
							},
							{
								name: "Authentication 2fa",
								link: "/profile/2fa",
							},
						]}
					/>

					{userData() && userData().role === "Admin" && <RouteAdmin />}
					{userData() && userData().role === "Cancer Caregiver" && (
						<RouteCaregivers />
					)}
					{userData() && userData().role === "Cancer Care Professional" && (
						<RouteCareProfessional />
					)}
					{/* end private route */}

					{/* this should always in the bottom */}

					<Route exact path="/*" component={Error404} />
					<Route exact path="/500" component={Error500} />
				</Switch>
			</Router>
		</QueryClientProvider>
	);
}
