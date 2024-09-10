"use strict";(self.webpackChunkdocs=self.webpackChunkdocs||[]).push([[7482],{5776:(e,t,i)=>{i.r(t),i.d(t,{assets:()=>c,contentTitle:()=>s,default:()=>l,frontMatter:()=>o,metadata:()=>d,toc:()=>a});var n=i(4848),r=i(8453);const o={title:"Create a custom identity provider",sidebar_label:"Custom Identity Providers",sidebar_position:2},s=void 0,d={id:"Guides/identity-providers",title:"Create a custom identity provider",description:"Identity providers return the current identity. Most of the time, this means the logged in user. LmcRbacMvc comes with a",source:"@site/versioned_docs/version-4.0/Guides/identity-providers.md",sourceDirName:"Guides",slug:"/Guides/identity-providers",permalink:"/LmcRbacMvc/docs/Guides/identity-providers",draft:!1,unlisted:!1,editUrl:"https://github.com/lm-commons/lmcrbacmvc/tree/master/docs/versioned_docs/version-4.0/Guides/identity-providers.md",tags:[],version:"4.0",sidebarPosition:2,frontMatter:{title:"Create a custom identity provider",sidebar_label:"Custom Identity Providers",sidebar_position:2},sidebar:"tutorialSidebar",previous:{title:"A Real World Example",permalink:"/LmcRbacMvc/docs/Guides/example"},next:{title:"Using LmcRbacMvc and LmcUser",permalink:"/LmcRbacMvc/docs/Guides/lmcuser"}},c={},a=[{value:"Create your own identity provider",id:"create-your-own-identity-provider",level:3}];function u(e){const t={code:"code",h3:"h3",p:"p",pre:"pre",...(0,r.R)(),...e.components};return(0,n.jsxs)(n.Fragment,{children:[(0,n.jsxs)(t.p,{children:["Identity providers return the current identity. Most of the time, this means the logged in user. LmcRbacMvc comes with a\ndefault identity provider (",(0,n.jsx)(t.code,{children:"Lmc\\Rbac\\Mvc\\Identity\\AuthenticationIdentityProvider"}),") that uses the\n",(0,n.jsx)(t.code,{children:"Laminas\\Authentication\\AuthenticationService"})," service."]}),"\n",(0,n.jsx)(t.h3,{id:"create-your-own-identity-provider",children:"Create your own identity provider"}),"\n",(0,n.jsxs)(t.p,{children:["If you want to implement your own identity provider, create a new class that implements\n",(0,n.jsx)(t.code,{children:"Lmc\\Rbac\\Mvc\\Identity\\IdentityProviderInterface"})," class. Then, change the ",(0,n.jsx)(t.code,{children:"identity_provider"})," option in LmcRbacMvc config,\nas shown below:"]}),"\n",(0,n.jsx)(t.pre,{children:(0,n.jsx)(t.code,{className:"language-php",children:"return [\n    'lmc_rbac' => [\n        'identity_provider' => 'MyCustomIdentityProvider'\n    ]\n];\n"})}),"\n",(0,n.jsx)(t.p,{children:"The identity provider is automatically pulled from the service manager."})]})}function l(e={}){const{wrapper:t}={...(0,r.R)(),...e.components};return t?(0,n.jsx)(t,{...e,children:(0,n.jsx)(u,{...e})}):u(e)}},8453:(e,t,i)=>{i.d(t,{R:()=>s,x:()=>d});var n=i(6540);const r={},o=n.createContext(r);function s(e){const t=n.useContext(o);return n.useMemo((function(){return"function"==typeof e?e(t):{...t,...e}}),[t,e])}function d(e){let t;return t=e.disableParentContext?"function"==typeof e.components?e.components(r):e.components||r:s(e.components),n.createElement(o.Provider,{value:t},e.children)}}}]);