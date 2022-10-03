import React from "react";
import ReactDOM from "react-dom";
import * as serviceWorkerRegistration from "./serviceWorkerRegistration";
import reportWebVitals from "./reportWebVitals";
import Routes from "./components/routes/Routes";

ReactDOM.render(<Routes />, document.getElementById("root"));
const time = Date.now();
// If you want your app to work offline and load faster, you can change
// unregister() to register() below. Note this comes with some pitfalls.
// Learn more about service workers: https://cra.link/PWA
serviceWorkerRegistration.register({
	onSuccess(registration) {
		console.debug("serviceWorkerRegistration success");
	},
	onUpdate(registration) {
		console.debug("serviceWorkerRegistration updated", Date.now() - time.current);
		const refresh = async () => {
			await registration?.waiting.postMessage({ type: "SKIP_WAITING" }); //send message to update the code (stop waiting)
			if ("caches" in window) {
				//delete cache, i think is no necessary but you lose nothing
				const names = await caches.keys();
				for (const name of names) {
					await caches.delete(name);
				}
			}
			window.location.reload();
		};
		if (Date.now() - time.current <= 2000) {
			return refresh();
		}
		// logicToShowPopup({
		// 	onClick: refresh,
		// });
	},
});

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();

// npm install -g serve
// serve -s build
// serviceWorkerRegistration.unregister(); to serviceWorkerRegistration.register();
// npm run build
